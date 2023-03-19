<?

/* Some code examples for TbsZip
Skrol29, 2010-09-03
*/


include_once('tbszip.php'); // load the TbsZip library
$zip = new clsTbsZip(); // create a new instance of the TbsZip class


$zip->CreateNew(); // start a new empty archive for adding files
// or
$zip->Open('an_existing_archive.zip'); // open an existing archive for reading and/or modifying


// --------------------------------------------------
// Reading information and data in the opened archive
// --------------------------------------------------

// check if a file is existing in the archive, the name must precise subfolders if any
$ok = $zip->FileExists('subfolder/help.html');

// count the files stored in the archive
$file_nbr = count($zip->CdFileLst);

// retrieve the content of an compressed file in the archive
$text1 = $zip->FileRead('readme.txt');

// retrieve the content of an compressed file in a subfolder of the archive
$text2 = $zip->FileRead('subfolder/readme.txt');


if ($ok) $zip->FileExists('subfolder/help.html');

// -----------------------------
// Modifying data in the archive
// -----------------------------

// add a file in the archive
$zip->FileAdd('newfile.txt', $data, TBSZIP_STRING); // add the file by giving the content
$zip->FileAdd('newpic1.png', './images/localpic1.png', TBSZIP_FILE);        // add the file by copying a local file
$zip->FileAdd('newpic2.png', './images/localpic2.png', TBSZIP_FILE, false); // add the uncompressed file by copying a local file

// delete an existing file in the archive
$zip->FileReplace('newfile.txt', $data, TBSZIP_STRING); // replace the file by giving the content
$zip->FileReplace('newpic1.png', './images/localpic1.png', TBSZIP_FILE);        // replace the file by copying a local file
$zip->FileReplace('newpic2.png', './images/localpic2.png', TBSZIP_FILE, false); // replace the uncompressed file by copying a local file
$zip->FileReplace('newpic3.png', false);                                        // delete the file in the archive

// cancel the last modification on the file (add/replace/delete)
$zip->FileCancelModif('newpic2.png');

// ----------------------
// Applying modifications
// ----------------------

$zip->Flush(TBSZIP_FILE, './save/new_archive.zip'); // apply modifications as a new local file

// apply modifications as an HTTP downloaded file
$zip->Flush(TBSZIP_DOWNLOAD, 'download.zip');
$zip->Flush(TBSZIP_DOWNLOAD, 'download.zip', 'application/zip'); // with a specific Content-Type

// apply modifications as a downloaded file with your customized HTTP headers
header("Content-type: application/force-download");
header("Content-Disposition: attachment; filename=download.zip");
header("Expires: Fri, 01 Jan 2010 05:00:00 GMT");
$zip->Flush(TBSZIP_DOWNLOAD+TBSZIP_NOHEADER);


// -----------------
// Close the archive
// -----------------

$zip->Close(); // stop to work with the opened archive. Modifications are not applied to the opened archive, use Flush() to commit  