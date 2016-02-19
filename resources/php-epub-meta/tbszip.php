<?php

/*
TbsZip version 2.15
Date    : 2013-10-16
Author  : Skrol29 (email: http://www.tinybutstrong.com/onlyyou.html)
Licence : LGPL
This class is independent from any other classes and has been originally created for the OpenTbs plug-in
for TinyButStrong Template Engine (TBS). OpenTbs makes TBS able to merge OpenOffice and Ms Office documents.
Visit http://www.tinybutstrong.com
*/

define('TBSZIP_DOWNLOAD',1);   // download (default)
define('TBSZIP_NOHEADER',4);   // option to use with DOWNLOAD: no header is sent
define('TBSZIP_FILE',8);       // output to file  , or add from file
define('TBSZIP_STRING',32);    // output to string, or add from string

class clsTbsZip {

	function __construct() {
		$this->Meth8Ok = extension_loaded('zlib'); // check if Zlib extension is available. This is need for compress and uncompress with method 8.
		$this->DisplayError = true;
		$this->ArchFile = '';
		$this->Error = false;
	}

	function CreateNew($ArchName='new.zip') {
	// Create a new virtual empty archive, the name will be the default name when the archive is flushed.
		if (!isset($this->Meth8Ok)) $this->__construct();  // for PHP 4 compatibility
		$this->Close(); // note that $this->ArchHnd is set to false here
		$this->Error = false;
		$this->ArchFile = $ArchName;
		$this->ArchIsNew = true;
		$bin = 'PK'.chr(05).chr(06).str_repeat(chr(0), 18);
		$this->CdEndPos = strlen($bin) - 4;
		$this->CdInfo = array('disk_num_curr'=>0, 'disk_num_cd'=>0, 'file_nbr_curr'=>0, 'file_nbr_tot'=>0, 'l_cd'=>0, 'p_cd'=>0, 'l_comm'=>0, 'v_comm'=>'', 'bin'=>$bin);
		$this->CdPos = $this->CdInfo['p_cd'];
	}

	function Open($ArchFile, $UseIncludePath=false) {
	// Open the zip archive
		if (!isset($this->Meth8Ok)) $this->__construct();  // for PHP 4 compatibility
		$this->Close(); // close handle and init info
		$this->Error = false;
		$this->ArchIsNew = false;
		$this->ArchIsStream = (is_resource($ArchFile) && (get_resource_type($ArchFile)=='stream'));
		if ($this->ArchIsStream) {
			$this->ArchFile = 'from_stream.zip';
			$this->ArchHnd = $ArchFile;
		} else {
			// open the file
			$this->ArchFile = $ArchFile;
			$this->ArchHnd = fopen($ArchFile, 'rb', $UseIncludePath);
		}
		$ok = !($this->ArchHnd===false);
		if ($ok) $ok = $this->CentralDirRead();
		return $ok;
	}

	function Close() {
		if (isset($this->ArchHnd) and ($this->ArchHnd!==false)) fclose($this->ArchHnd);
		$this->ArchFile = '';
		$this->ArchHnd = false;
		$this->CdInfo = array();
		$this->CdFileLst = array();
		$this->CdFileNbr = 0;
		$this->CdFileByName = array();
		$this->VisFileLst = array();
		$this->ArchCancelModif();
	}

	function ArchCancelModif() {
		$this->LastReadComp = false; // compression of the last read file (1=compressed, 0=stored not compressed, -1= stored compressed but read uncompressed)
		$this->LastReadIdx = false;  // index of the last file read
		$this->ReplInfo = array();
		$this->ReplByPos = array();
		$this->AddInfo = array();
	}

	function FileAdd($Name, $Data, $DataType=TBSZIP_STRING, $Compress=true) {

		if ($Data===false) return $this->FileCancelModif($Name, false); // Cancel a previously added file

		// Save information for adding a new file into the archive
		$Diff = 30 + 46 + 2*strlen($Name); // size of the header + cd info
		$Ref = $this->_DataCreateNewRef($Data, $DataType, $Compress, $Diff, $Name);
		if ($Ref===false) return false;
		$Ref['name'] = $Name;
		$this->AddInfo[] = $Ref;
		return $Ref['res'];

	}

	function CentralDirRead() {
		$cd_info = 'PK'.chr(05).chr(06); // signature of the Central Directory
		$cd_pos = -22;
		$this->_MoveTo($cd_pos, SEEK_END);
		$b = $this->_ReadData(4);
		if ($b===$cd_info) {
			$this->CdEndPos = ftell($this->ArchHnd) - 4;
		} else {
			$p = $this->_FindCDEnd($cd_info);
			//echo 'p='.var_export($p,true); exit;
			if ($p===false) {
				return $this->RaiseError('The End of Central Directory Record is not found.');
			} else {
				$this->CdEndPos = $p;
				$this->_MoveTo($p+4);
			}
		}
		$this->CdInfo = $this->CentralDirRead_End($cd_info);
		$this->CdFileLst = array();
		$this->CdFileNbr = $this->CdInfo['file_nbr_curr'];
		$this->CdPos = $this->CdInfo['p_cd'];

		if ($this->CdFileNbr<=0) return $this->RaiseError('No header found in the Central Directory.');
		if ($this->CdPos<=0) return $this->RaiseError('No position found for the Central Directory.');

		$this->_MoveTo($this->CdPos);
		for ($i=0;$i<$this->CdFileNbr;$i++) {
			$x = $this->CentralDirRead_File($i);
			if ($x!==false) {
				$this->CdFileLst[$i] = $x;
				$this->CdFileByName[$x['v_name']] = $i;
			}
		}
		return true;
	}

	function CentralDirRead_End($cd_info) {
		$b = $cd_info.$this->_ReadData(18);
		$x = array();
		$x['disk_num_curr'] = $this->_GetDec($b,4,2);  // number of this disk
		$x['disk_num_cd'] = $this->_GetDec($b,6,2);    // number of the disk with the start of the central directory
		$x['file_nbr_curr'] = $this->_GetDec($b,8,2);  // total number of entries in the central directory on this disk
		$x['file_nbr_tot'] = $this->_GetDec($b,10,2);  // total number of entries in the central directory
		$x['l_cd'] = $this->_GetDec($b,12,4);          // size of the central directory
		$x['p_cd'] = $this->_GetDec($b,16,4);          // position of start of central directory with respect to the starting disk number
		$x['l_comm'] = $this->_GetDec($b,20,2);        // .ZIP file comment length
		$x['v_comm'] = $this->_ReadData($x['l_comm']); // .ZIP file comment
		$x['bin'] = $b.$x['v_comm'];
		return $x;
	}

	function CentralDirRead_File($idx) {

		$b = $this->_ReadData(46);

		$x = $this->_GetHex($b,0,4);
		if ($x!=='h:02014b50') return $this->RaiseError("Signature of Central Directory Header #".$idx." (file information) expected but not found at position ".$this->_TxtPos(ftell($this->ArchHnd) - 46).".");

		$x = array();
		$x['vers_used'] = $this->_GetDec($b,4,2);
		$x['vers_necess'] = $this->_GetDec($b,6,2);
		$x['purp'] = $this->_GetBin($b,8,2);
		$x['meth'] = $this->_GetDec($b,10,2);
		$x['time'] = $this->_GetDec($b,12,2);
		$x['date'] = $this->_GetDec($b,14,2);
		$x['crc32'] = $this->_GetDec($b,16,4);
		$x['l_data_c'] = $this->_GetDec($b,20,4);
		$x['l_data_u'] = $this->_GetDec($b,24,4);
		$x['l_name'] = $this->_GetDec($b,28,2);
		$x['l_fields'] = $this->_GetDec($b,30,2);
		$x['l_comm'] = $this->_GetDec($b,32,2);
		$x['disk_num'] = $this->_GetDec($b,34,2);
		$x['int_file_att'] = $this->_GetDec($b,36,2);
		$x['ext_file_att'] = $this->_GetDec($b,38,4);
		$x['p_loc'] = $this->_GetDec($b,42,4);
		$x['v_name'] = $this->_ReadData($x['l_name']);
		$x['v_fields'] = $this->_ReadData($x['l_fields']);
		$x['v_comm'] = $this->_ReadData($x['l_comm']);

		$x['bin'] = $b.$x['v_name'].$x['v_fields'].$x['v_comm'];

		return $x;
	}

	function RaiseError($Msg) {
		if ($this->DisplayError) {
			if (PHP_SAPI==='cli') {
				echo get_class($this).' ERROR with the zip archive: '.$Msg."\r\n";
			} else {
				echo '<strong>'.get_class($this).' ERROR with the zip archive:</strong> '.$Msg.'<br>'."\r\n";
			}
		}
		$this->Error = $Msg;
		return false;
	}

	function Debug($FileHeaders=false) {

		$this->DisplayError = true;

		if ($FileHeaders) {
			// Calculations first in order to have error messages before other information
			$idx = 0;
			$pos = 0;
			$pos_stop = $this->CdInfo['p_cd'];
			$this->_MoveTo($pos);
			while ( ($pos<$pos_stop) && ($ok = $this->_ReadFile($idx,false)) ) {
				$this->VisFileLst[$idx]['p_this_header (debug_mode only)'] = $pos;
				$pos = ftell($this->ArchHnd);
				$idx++;
			}
		}

		$nl = "\r\n";
		echo "<pre>";

		echo "-------------------------------".$nl;
		echo "End of Central Directory record".$nl;
		echo "-------------------------------".$nl;
		print_r($this->DebugArray($this->CdInfo));

		echo $nl;
		echo "-------------------------".$nl;
		echo "Central Directory headers".$nl;
		echo "-------------------------".$nl;
		print_r($this->DebugArray($this->CdFileLst));

		if ($FileHeaders) {
			echo $nl;
			echo "------------------".$nl;
			echo "Local File headers".$nl;
			echo "------------------".$nl;
			print_r($this->DebugArray($this->VisFileLst));
		}

		echo "</pre>";

	}

	function DebugArray($arr) {
		foreach ($arr as $k=>$v) {
			if (is_array($v)) {
				$arr[$k] = $this->DebugArray($v);
			} elseif (substr($k,0,2)=='p_') {
				$arr[$k] = $this->_TxtPos($v);
			}
		}
		return $arr;
	}

	function FileExists($NameOrIdx) {
		return ($this->FileGetIdx($NameOrIdx)!==false);
	}

	function FileGetIdx($NameOrIdx) {
	// Check if a file name, or a file index exists in the Central Directory, and return its index
		if (is_string($NameOrIdx)) {
			if (isset($this->CdFileByName[$NameOrIdx])) {
				return $this->CdFileByName[$NameOrIdx];
			} else {
				return false;
			}
		} else {
			if (isset($this->CdFileLst[$NameOrIdx])) {
				return $NameOrIdx;
			} else {
				return false;
			}
		}
	}

	function FileGetIdxAdd($Name) {
	// Check if a file name exists in the list of file to add, and return its index
		if (!is_string($Name)) return false;
		$idx_lst = array_keys($this->AddInfo);
		foreach ($idx_lst as $idx) {
			if ($this->AddInfo[$idx]['name']===$Name) return $idx;
		}
		return false;
	}

	function FileRead($NameOrIdx, $Uncompress=true) {

		$this->LastReadComp = false; // means the file is not found
		$this->LastReadIdx = false;

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) return $this->RaiseError('File "'.$NameOrIdx.'" is not found in the Central Directory.');

		$pos = $this->CdFileLst[$idx]['p_loc'];
		$this->_MoveTo($pos);

		$this->LastReadIdx = $idx; // Can be usefull to get the idx

		$Data = $this->_ReadFile($idx, true);

		// Manage uncompression
		$Comp = 1; // means the contents stays compressed
		$meth = $this->CdFileLst[$idx]['meth'];
		if ($meth==8) {
			if ($Uncompress) {
				if ($this->Meth8Ok) {
					$Data = gzinflate($Data);
					$Comp = -1; // means uncompressed
				} else {
					$this->RaiseError('Unable to uncompress file "'.$NameOrIdx.'" because extension Zlib is not installed.');
				}
			}
		} elseif($meth==0) {
			$Comp = 0; // means stored without compression
		} else {
			if ($Uncompress) $this->RaiseError('Unable to uncompress file "'.$NameOrIdx.'" because it is compressed with method '.$meth.'.');
		}
		$this->LastReadComp = $Comp;

		return $Data;

	}

	function _ReadFile($idx, $ReadData) {
	// read the file header (and maybe the data ) in the archive, assuming the cursor in at a new file position

		$b = $this->_ReadData(30);

		$x = $this->_GetHex($b,0,4);
		if ($x!=='h:04034b50') return $this->RaiseError("Signature of Local File Header #".$idx." (data section) expected but not found at position ".$this->_TxtPos(ftell($this->ArchHnd)-30).".");

		$x = array();
		$x['vers'] = $this->_GetDec($b,4,2);
		$x['purp'] = $this->_GetBin($b,6,2);
		$x['meth'] = $this->_GetDec($b,8,2);
		$x['time'] = $this->_GetDec($b,10,2);
		$x['date'] = $this->_GetDec($b,12,2);
		$x['crc32'] = $this->_GetDec($b,14,4);
		$x['l_data_c'] = $this->_GetDec($b,18,4);
		$x['l_data_u'] = $this->_GetDec($b,22,4);
		$x['l_name'] = $this->_GetDec($b,26,2);
		$x['l_fields'] = $this->_GetDec($b,28,2);
		$x['v_name'] = $this->_ReadData($x['l_name']);
		$x['v_fields'] = $this->_ReadData($x['l_fields']);

		$x['bin'] = $b.$x['v_name'].$x['v_fields'];

		// Read Data
		if (isset($this->CdFileLst[$idx])) {
			$len_cd = $this->CdFileLst[$idx]['l_data_c'];
			if ($x['l_data_c']==0) {
				// Sometimes, the size is not specified in the local information.
				$len = $len_cd;
			} else {
				$len = $x['l_data_c'];
				if ($len!=$len_cd) {
					//echo "TbsZip Warning: Local information for file #".$idx." says len=".$len.", while Central Directory says len=".$len_cd.".";
				}
			}
		} else {
			$len = $x['l_data_c'];
			if ($len==0) $this->RaiseError("File Data #".$idx." cannt be read because no length is specified in the Local File Header and its Central Directory information has not been found.");
		}

		if ($ReadData) {
			$Data = $this->_ReadData($len);
		} else {
			$this->_MoveTo($len, SEEK_CUR);
		}

		// Description information
		$desc_ok = ($x['purp'][2+3]=='1');
		if ($desc_ok) {
			$b = $this->_ReadData(12);
			$s = $this->_GetHex($b,0,4);
			$d = 0;
			// the specification says the signature may or may not be present
			if ($s=='h:08074b50') {
				$b .= $this->_ReadData(4); 
				$d = 4;
				$x['desc_bin'] = $b;
				$x['desc_sign'] = $s;
			} else {
				$x['desc_bin'] = $b;
			}
			$x['desc_crc32']    = $this->_GetDec($b,0+$d,4);
			$x['desc_l_data_c'] = $this->_GetDec($b,4+$d,4);
			$x['desc_l_data_u'] = $this->_GetDec($b,8+$d,4);
		}

		// Save file info without the data
		$this->VisFileLst[$idx] = $x;

		// Return the info
		if ($ReadData) {
			return $Data;
		} else {
			return true;
		}

	}

	function FileReplace($NameOrIdx, $Data, $DataType=TBSZIP_STRING, $Compress=true) {
	// Store replacement information.

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) return $this->RaiseError('File "'.$NameOrIdx.'" is not found in the Central Directory.');

		$pos = $this->CdFileLst[$idx]['p_loc'];

		if ($Data===false) {
			// file to delete
			$this->ReplInfo[$idx] = false;
			$Result = true;
		} else {
			// file to replace
			$Diff = - $this->CdFileLst[$idx]['l_data_c'];
			$Ref = $this->_DataCreateNewRef($Data, $DataType, $Compress, $Diff, $NameOrIdx);
			if ($Ref===false) return false;
			$this->ReplInfo[$idx] = $Ref;
			$Result = $Ref['res'];
		}

		$this->ReplByPos[$pos] = $idx;

		return $Result;

	}

	/**
	 * Return the state of the file.
	 * @return {string} 'u'=unchanged, 'm'=modified, 'd'=deleted, 'a'=added, false=unknown
	 */
	function FileGetState($NameOrIdx) {

		$idx = $this->FileGetIdx($NameOrIdx);
		if ($idx===false) {
			$idx = $this->FileGetIdxAdd($NameOrIdx);
			if ($idx===false) {
				return false;
			} else {
				return 'a';
			}
		} elseif (isset($this->ReplInfo[$idx])) {
			if ($this->ReplInfo[$idx]===false) {
				return 'd';
			} else {
				return 'm';
			}
		} else {
			return 'u';
		}

	}

	function FileCancelModif($NameOrIdx, $ReplacedAndDeleted=true) {
	// cancel added, modified or deleted modifications on a file in the archive
	// return the number of cancels

		$nbr = 0;

		if ($ReplacedAndDeleted) {
			// replaced or deleted files
			$idx = $this->FileGetIdx($NameOrIdx);
			if ($idx!==false) {
				if (isset($this->ReplInfo[$idx])) {
					$pos = $this->CdFileLst[$idx]['p_loc'];
					unset($this->ReplByPos[$pos]);
					unset($this->ReplInfo[$idx]);
					$nbr++;
				}
			}
		}

		// added files
		$idx = $this->FileGetIdxAdd($NameOrIdx);
		if ($idx!==false) {
			unset($this->AddInfo[$idx]);
			$nbr++;
		}

		return $nbr;

	}

	function Flush($Render=TBSZIP_DOWNLOAD, $File='', $ContentType='') {

		if ( ($File!=='') && ($this->ArchFile===$File)) {
			$this->RaiseError('Method Flush() cannot overwrite the current opened archive: \''.$File.'\''); // this makes corrupted zip archives without PHP error.
			return false;
		}

		$ArchPos = 0;
		$Delta = 0;
		$FicNewPos = array();
		$DelLst = array(); // idx of deleted files
		$DeltaCdLen = 0; // delta of the CD's size

		$now = time();
		$date  = $this->_MsDos_Date($now);
		$time  = $this->_MsDos_Time($now);

		if (!$this->OutputOpen($Render, $File, $ContentType)) return false;

		// output modified zipped files and unmodified zipped files that are beetween them
		ksort($this->ReplByPos);
		foreach ($this->ReplByPos as $ReplPos => $ReplIdx) {
			// output data from the zip archive which is before the data to replace
			$this->OutputFromArch($ArchPos, $ReplPos);
			// get current file information
			if (!isset($this->VisFileLst[$ReplIdx])) $this->_ReadFile($ReplIdx, false);
			$FileInfo =& $this->VisFileLst[$ReplIdx];
			$b1 = $FileInfo['bin'];
			if (isset($FileInfo['desc_bin'])) {
				$b2 = $FileInfo['desc_bin'];
			} else {
				$b2 = '';
			}
			$info_old_len = strlen($b1) + $this->CdFileLst[$ReplIdx]['l_data_c'] + strlen($b2); // $FileInfo['l_data_c'] may have a 0 value in some archives
			// get replacement information
			$ReplInfo =& $this->ReplInfo[$ReplIdx];
			if ($ReplInfo===false) {
				// The file is to be deleted
				$Delta = $Delta - $info_old_len; // headers and footers are also deleted
				$DelLst[$ReplIdx] = true;
			} else {
				// prepare the header of the current file
				$this->_DataPrepare($ReplInfo); // get data from external file if necessary
				$this->_PutDec($b1, $time, 10, 2); // time
				$this->_PutDec($b1, $date, 12, 2); // date
				$this->_PutDec($b1, $ReplInfo['crc32'], 14, 4); // crc32
				$this->_PutDec($b1, $ReplInfo['len_c'], 18, 4); // l_data_c
				$this->_PutDec($b1, $ReplInfo['len_u'], 22, 4); // l_data_u
				if ($ReplInfo['meth']!==false) $this->_PutDec($b1, $ReplInfo['meth'], 8, 2); // meth
				// prepare the bottom description if the zipped file, if any
				if ($b2!=='') {
					$d = (strlen($b2)==16) ? 4 : 0; // offset because of the signature if any
					$this->_PutDec($b2, $ReplInfo['crc32'], $d+0, 4); // crc32
					$this->_PutDec($b2, $ReplInfo['len_c'], $d+4, 4); // l_data_c
					$this->_PutDec($b2, $ReplInfo['len_u'], $d+8, 4); // l_data_u
				}
				// output data
				$this->OutputFromString($b1.$ReplInfo['data'].$b2);
				unset($ReplInfo['data']); // save PHP memory
				$Delta = $Delta + $ReplInfo['diff'] + $ReplInfo['len_c'];
			}
			// Update the delta of positions for zipped files which are physically after the currently replaced one
			for ($i=0;$i<$this->CdFileNbr;$i++) {
				if ($this->CdFileLst[$i]['p_loc']>$ReplPos) {
					$FicNewPos[$i] = $this->CdFileLst[$i]['p_loc'] + $Delta;
				}
			}
			// Update the current pos in the archive
			$ArchPos = $ReplPos + $info_old_len;
		}

		// Ouput all the zipped files that remain before the Central Directory listing
		if ($this->ArchHnd!==false) $this->OutputFromArch($ArchPos, $this->CdPos); // ArchHnd is false if CreateNew() has been called
		$ArchPos = $this->CdPos;

		// Output file to add
		$AddNbr = count($this->AddInfo);
		$AddDataLen = 0; // total len of added data (inlcuding file headers)
		if ($AddNbr>0) {
			$AddPos = $ArchPos + $Delta; // position of the start
			$AddLst = array_keys($this->AddInfo);
			foreach ($AddLst as $idx) {
				$n = $this->_DataOuputAddedFile($idx, $AddPos);
				$AddPos += $n;
				$AddDataLen += $n;
			}
		}

		// Modifiy file information in the Central Directory for replaced files
		$b2 = '';
		$old_cd_len = 0;
		for ($i=0;$i<$this->CdFileNbr;$i++) {
			$b1 = $this->CdFileLst[$i]['bin'];
			$old_cd_len += strlen($b1);
			if (!isset($DelLst[$i])) {
				if (isset($FicNewPos[$i])) $this->_PutDec($b1, $FicNewPos[$i], 42, 4);   // p_loc
				if (isset($this->ReplInfo[$i])) {
					$ReplInfo =& $this->ReplInfo[$i];
					$this->_PutDec($b1, $time, 12, 2); // time
					$this->_PutDec($b1, $date, 14, 2); // date
					$this->_PutDec($b1, $ReplInfo['crc32'], 16, 4); // crc32
					$this->_PutDec($b1, $ReplInfo['len_c'], 20, 4); // l_data_c
					$this->_PutDec($b1, $ReplInfo['len_u'], 24, 4); // l_data_u
					if ($ReplInfo['meth']!==false) $this->_PutDec($b1, $ReplInfo['meth'], 10, 2); // meth
				}
				$b2 .= $b1;
			}
		}
		$this->OutputFromString($b2);
		$ArchPos += $old_cd_len;
 		$DeltaCdLen =  $DeltaCdLen + strlen($b2) - $old_cd_len;
 
		// Output until "end of central directory record"
		if ($this->ArchHnd!==false) $this->OutputFromArch($ArchPos, $this->CdEndPos); // ArchHnd is false if CreateNew() has been called

		// Output file information of the Central Directory for added files
		if ($AddNbr>0) {
			$b2 = '';
			foreach ($AddLst as $idx) {
				$b2 .= $this->AddInfo[$idx]['bin'];
			}
			$this->OutputFromString($b2);
			$DeltaCdLen += strlen($b2);
		}

		// Output "end of central directory record"
		$b2 = $this->CdInfo['bin'];
		$DelNbr = count($DelLst);
		if ( ($AddNbr>0) or ($DelNbr>0) ) {
			// total number of entries in the central directory on this disk
			$n = $this->_GetDec($b2, 8, 2);
			$this->_PutDec($b2, $n + $AddNbr - $DelNbr,  8, 2);
			// total number of entries in the central directory
			$n = $this->_GetDec($b2, 10, 2);
			$this->_PutDec($b2, $n + $AddNbr - $DelNbr, 10, 2);
			// size of the central directory
			$n = $this->_GetDec($b2, 12, 4);
			$this->_PutDec($b2, $n + $DeltaCdLen, 12, 4);
			$Delta = $Delta + $AddDataLen;
		}
		$this->_PutDec($b2, $this->CdPos+$Delta , 16, 4); // p_cd  (offset of start of central directory with respect to the starting disk number)
		$this->OutputFromString($b2);

		$this->OutputClose();

		return true;

	}

	// ----------------
	// output functions
	// ----------------

	function OutputOpen($Render, $File, $ContentType) {

		if (($Render & TBSZIP_FILE)==TBSZIP_FILE) {
			$this->OutputMode = TBSZIP_FILE;
			if (''.$File=='') $File = basename($this->ArchFile).'.zip';
			$this->OutputHandle = @fopen($File, 'w');
			if ($this->OutputHandle===false) {
				return $this->RaiseError('Method Flush() cannot overwrite the target file \''.$File.'\'. This may not be a valid file path or the file may be locked by another process or because of a denied permission.');
			}
		} elseif (($Render & TBSZIP_STRING)==TBSZIP_STRING) {
			$this->OutputMode = TBSZIP_STRING;
			$this->OutputSrc = '';
		} elseif (($Render & TBSZIP_DOWNLOAD)==TBSZIP_DOWNLOAD) {
			$this->OutputMode = TBSZIP_DOWNLOAD;
			// Output the file
			if (''.$File=='') $File = basename($this->ArchFile);
			if (($Render & TBSZIP_NOHEADER)==TBSZIP_NOHEADER) {
			} else {
				header ('Pragma: no-cache');
				if ($ContentType!='') header ('Content-Type: '.$ContentType);
				if (strlen($File) != strlen(utf8_decode($File))) {
					header('Content-Disposition: attachment; filename="book.epub"; filename*=utf-8\'\'' . rawurlencode($File));
				} else {
					header('Content-Disposition: attachment; filename="'.$File.'"');
				}
				header('Expires: 0');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Cache-Control: public');
				header('Content-Description: File Transfer'); 
				header('Content-Transfer-Encoding: binary');
				$Len = $this->_EstimateNewArchSize();
				if ($Len!==false) header('Content-Length: '.$Len); 
			}
		} else {
			return $this->RaiseError('Method Flush is called with a unsupported render option.');
		}

		return true;

	}

	function OutputFromArch($pos, $pos_stop) {
		$len = $pos_stop - $pos;
		if ($len<0) return;
		$this->_MoveTo($pos);
		$block = 1024;
		while ($len>0) {
			$l = min($len, $block);
			$x = $this->_ReadData($l);
			$this->OutputFromString($x);
			$len = $len - $l;
		}
		unset($x);
	}

	function OutputFromString($data) {
		if ($this->OutputMode===TBSZIP_DOWNLOAD) {
			echo $data; // donwload
		} elseif ($this->OutputMode===TBSZIP_STRING) {
			$this->OutputSrc .= $data; // to string
		} elseif (TBSZIP_FILE) {
			fwrite($this->OutputHandle, $data); // to file
		}
	}

	function OutputClose() {
		if ( ($this->OutputMode===TBSZIP_FILE) && ($this->OutputHandle!==false) ) {
			fclose($this->OutputHandle);
			$this->OutputHandle = false;
		}
	}

	// ----------------
	// Reading functions
	// ----------------

	function _MoveTo($pos, $relative = SEEK_SET) {
		fseek($this->ArchHnd, $pos, $relative);
	}

	function _ReadData($len) {
		if ($len>0) {
			$x = fread($this->ArchHnd, $len);
			return $x;
		} else {
			return '';
		}
	}

	// ----------------
	// Take info from binary data
	// ----------------

	function _GetDec($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		$z = 0;
		for ($i=0;$i<$len;$i++) {
			$asc = ord($x[$i]);
			if ($asc>0) $z = $z + $asc*pow(256,$i);
		}
		return $z;
	}

	function _GetHex($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		return 'h:'.bin2hex(strrev($x));
	}

	function _GetBin($txt, $pos, $len) {
		$x = substr($txt, $pos, $len);
		$z = '';
		for ($i=0;$i<$len;$i++) {
			$asc = ord($x[$i]);
			if (isset($x[$i])) {
				for ($j=0;$j<8;$j++) {
					$z .= ($asc & pow(2,$j)) ? '1' : '0';
				}
			} else {
				$z .= '00000000';
			}
		}
		return 'b:'.$z;
	}

	// ----------------
	// Put info into binary data
	// ----------------

	function _PutDec(&$txt, $val, $pos, $len) {
		$x = '';
		for ($i=0;$i<$len;$i++) {
			if ($val==0) {
				$z = 0;
			} else {
				$z = intval($val % 256);
				if (($val<0) && ($z!=0)) { // ($z!=0) is very important, example: val=-420085702
					// special opration for negative value. If the number id too big, PHP stores it into a signed integer. For example: crc32('coucou') => -256185401 instead of  4038781895. NegVal = BigVal - (MaxVal+1) = BigVal - 256^4
					$val = ($val - $z)/256 -1;
					$z = 256 + $z;
				} else {
					$val = ($val - $z)/256;
				}
			}
			$x .= chr($z);
		}
		$txt = substr_replace($txt, $x, $pos, $len);
	}

	function _MsDos_Date($Timestamp = false) {
		// convert a date-time timstamp into the MS-Dos format
		$d = ($Timestamp===false) ? getdate() : getdate($Timestamp);
		return (($d['year']-1980)*512) + ($d['mon']*32) + $d['mday'];
	}
	function _MsDos_Time($Timestamp = false) {
		// convert a date-time timstamp into the MS-Dos format
		$d = ($Timestamp===false) ? getdate() : getdate($Timestamp);
		return ($d['hours']*2048) + ($d['minutes']*32) + intval($d['seconds']/2); // seconds are rounded to an even number in order to save 1 bit
	}

	function _MsDos_Debug($date, $time) {
		// Display the formated date and time. Just for debug purpose.
		// date end time are encoded on 16 bits (2 bytes) : date = yyyyyyymmmmddddd , time = hhhhhnnnnnssssss
		$y = ($date & 65024)/512 + 1980;
		$m = ($date & 480)/32;
		$d = ($date & 31);
		$h = ($time & 63488)/2048;
		$i = ($time & 1984)/32;
		$s = ($time & 31) * 2; // seconds have been rounded to an even number in order to save 1 bit
		return $y.'-'.str_pad($m,2,'0',STR_PAD_LEFT).'-'.str_pad($d,2,'0',STR_PAD_LEFT).' '.str_pad($h,2,'0',STR_PAD_LEFT).':'.str_pad($i,2,'0',STR_PAD_LEFT).':'.str_pad($s,2,'0',STR_PAD_LEFT);
	}

	function _TxtPos($pos) {
		// Return the human readable position in both decimal and hexa
		return $pos." (h:".dechex($pos).")";
	}

	/**
	 * Search the record of end of the Central Directory.
	 * Return the position of the record in the file.
	 * Return false if the record is not found. The comment cannot exceed 65335 bytes (=FFFF).
	 * The method is read backwards a block of 256 bytes and search the key in this block.
	 */
	function _FindCDEnd($cd_info) {
		$nbr = 1;
		$p = false;
		$pos = ftell($this->ArchHnd) - 4 - 256;
		while ( ($p===false) && ($nbr<256) ) {
			if ($pos<=0) {
				$pos = 0;
				$nbr = 256; // in order to make this a last check
			}
			$this->_MoveTo($pos);
			$x = $this->_ReadData(256);
			$p = strpos($x, $cd_info);
			if ($p===false) {
				$nbr++;
				$pos = $pos - 256 - 256;
			} else {
				return $pos + $p;
			}
		}
		return false;
	}
	
	function _DataOuputAddedFile($Idx, $PosLoc) {

		$Ref =& $this->AddInfo[$Idx];
		$this->_DataPrepare($Ref); // get data from external file if necessary

		// Other info
		$now = time();
		$date  = $this->_MsDos_Date($now);
		$time  = $this->_MsDos_Time($now);
		$len_n = strlen($Ref['name']);
		$purp  = 2048 ; // purpose // +8 to indicates that there is an extended local header 

		// Header for file in the data section 
		$b = 'PK'.chr(03).chr(04).str_repeat(' ',26); // signature
		$this->_PutDec($b,20,4,2); //vers = 20
		$this->_PutDec($b,$purp,6,2); // purp
		$this->_PutDec($b,$Ref['meth'],8,2);  // meth
		$this->_PutDec($b,$time,10,2); // time
		$this->_PutDec($b,$date,12,2); // date
		$this->_PutDec($b,$Ref['crc32'],14,4); // crc32
		$this->_PutDec($b,$Ref['len_c'],18,4); // l_data_c
		$this->_PutDec($b,$Ref['len_u'],22,4); // l_data_u
		$this->_PutDec($b,$len_n,26,2); // l_name
		$this->_PutDec($b,0,28,2); // l_fields
		$b .= $Ref['name']; // name
		$b .= ''; // fields

		// Output the data
		$this->OutputFromString($b.$Ref['data']);
		$OutputLen = strlen($b) + $Ref['len_c']; // new position of the cursor
		unset($Ref['data']); // save PHP memory

		// Information for file in the Central Directory
		$b = 'PK'.chr(01).chr(02).str_repeat(' ',42); // signature
		$this->_PutDec($b,20,4,2);  // vers_used = 20
		$this->_PutDec($b,20,6,2);  // vers_necess = 20
		$this->_PutDec($b,$purp,8,2);  // purp
		$this->_PutDec($b,$Ref['meth'],10,2); // meth
		$this->_PutDec($b,$time,12,2); // time
		$this->_PutDec($b,$date,14,2); // date
		$this->_PutDec($b,$Ref['crc32'],16,4); // crc32
		$this->_PutDec($b,$Ref['len_c'],20,4); // l_data_c
		$this->_PutDec($b,$Ref['len_u'],24,4); // l_data_u
		$this->_PutDec($b,$len_n,28,2); // l_name
		$this->_PutDec($b,0,30,2); // l_fields
		$this->_PutDec($b,0,32,2); // l_comm
		$this->_PutDec($b,0,34,2); // disk_num
		$this->_PutDec($b,0,36,2); // int_file_att
		$this->_PutDec($b,0,38,4); // ext_file_att
		$this->_PutDec($b,$PosLoc,42,4); // p_loc
		$b .= $Ref['name']; // v_name
		$b .= ''; // v_fields
		$b .= ''; // v_comm

		$Ref['bin'] = $b;

		return $OutputLen;

	}

	function _DataCreateNewRef($Data, $DataType, $Compress, $Diff, $NameOrIdx) {

		if (is_array($Compress)) {
			$result = 2;
			$meth = $Compress['meth'];
			$len_u = $Compress['len_u'];
			$crc32 = $Compress['crc32'];
			$Compress = false;
		} elseif ($Compress and ($this->Meth8Ok)) {
			$result = 1;
			$meth = 8;
			$len_u = false; // means unknown
			$crc32 = false;
		} else {
			$result = ($Compress) ? -1 : 0;
			$meth = 0;
			$len_u = false;
			$crc32 = false;
			$Compress = false;
		}

		if ($DataType==TBSZIP_STRING) {
			$path = false;
			if ($Compress) {
				// we compress now in order to save PHP memory
				$len_u = strlen($Data);
				$crc32 = crc32($Data);
				$Data = gzdeflate($Data);
				$len_c = strlen($Data);
			} else {
				$len_c = strlen($Data);
				if ($len_u===false) {
					$len_u = $len_c;
					$crc32 = crc32($Data);
				}
			}
		} else {
			$path = $Data;
			$Data = false;
			if (file_exists($path)) {
				$fz = filesize($path);
				if ($len_u===false) $len_u = $fz;
				$len_c = ($Compress) ? false : $fz;
			} else {
				return $this->RaiseError("Cannot add the file '".$path."' because it is not found.");
			}
		}

		// at this step $Data and $crc32 can be false only in case of external file, and $len_c is false only in case of external file to compress
		return array('data'=>$Data, 'path'=>$path, 'meth'=>$meth, 'len_u'=>$len_u, 'len_c'=>$len_c, 'crc32'=>$crc32, 'diff'=>$Diff, 'res'=>$result);

	}

	function _DataPrepare(&$Ref) {
	// returns the real size of data
		if ($Ref['path']!==false) {
			$Ref['data'] = file_get_contents($Ref['path']);
			if ($Ref['crc32']===false) $Ref['crc32'] = crc32($Ref['data']);
			if ($Ref['len_c']===false) {
				// means the data must be compressed
				$Ref['data'] = gzdeflate($Ref['data']);
				$Ref['len_c'] = strlen($Ref['data']);
			}
		}
	}

	function _EstimateNewArchSize($Optim=true) {
	// Return the size of the new archive, or false if it cannot be calculated (because of external file that must be compressed before to be insered)

		if ($this->ArchIsNew) {
			$Len = strlen($this->CdInfo['bin']);
		} elseif ($this->ArchIsStream) {
			$x = fstat($this->ArchHnd);
			$Len = $x['size'];
		} else {
			$Len = filesize($this->ArchFile);
		}

		// files to replace or delete
		foreach ($this->ReplByPos as $i) {
			$Ref =& $this->ReplInfo[$i];
			if ($Ref===false) {
				// file to delete
				$Info =& $this->CdFileLst[$i];
				if (!isset($this->VisFileLst[$i])) {
					if ($Optim) return false; // if $Optimization is set to true, then we d'ont rewind to read information
					$this->_MoveTo($Info['p_loc']);
					$this->_ReadFile($i, false);
				}
				$Vis =& $this->VisFileLst[$i];
				$Len += -strlen($Vis['bin']) -strlen($Info['bin']) - $Info['l_data_c'];
				if (isset($Vis['desc_bin'])) $Len += -strlen($Vis['desc_bin']);
			} elseif ($Ref['len_c']===false) {
				return false; // information not yet known
			} else {
				// file to replace
				$Len += $Ref['len_c'] + $Ref['diff'];
			}
		}

		// files to add
		$i_lst = array_keys($this->AddInfo);
		foreach ($i_lst as $i) {
			$Ref =& $this->AddInfo[$i];
			if ($Ref['len_c']===false) {
				return false; // information not yet known
			} else {
				$Len += $Ref['len_c'] + $Ref['diff'];
			}
		}

		return $Len;

	}

}