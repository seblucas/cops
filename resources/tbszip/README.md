TbsZip - a zip modifier for PHP
===============================
version 2.16, 2014-04-08, by Skrol29  
document updated on 2014-04-08


Introduction
------------

**TbsZip** is a PHP class which can read a zip archive, and even edit sub-files
in the archive  and then create a new archive. It works without  any temporary
file.  While this class is independent from other libraries, it has been first
created for the [OpenTBS](http://www.tinybutstrong.com/plugins/opentbs/demo/demo.html)
project which is a plugin for the [TinyButStrong](http://www.tinybutstrong.com/)
Template Engine.  OpenTBS makes TinyButStrong able to build OpenOffice and Ms
Office documents with the technique of templates.

TbsZip is developed under PHP5 but should be compatible with PHP4.

See the [OpenTBS demo](http://www.tinybutstrong.com/plugins/opentbs/demo/demo.html).  
See other [TinyButStrong plugins](http://www.tinybutstrong.com/plugins.php).


Features
--------

* can read a common zip archive, or start with an empty archive
* can modify the content of files in the archive (replace, delete or add new file)
* the new file content can come from a PHP string, or an external physical file
* the modified archive can be released as a new physical file, an HTTP download, or
  a PHP string
* the original archive is not modified
* the class does not use temporary files: when the new archive is flushed, unmodified
  parts of the archives are directly streamed from the original archive, modified
  parts are streamed form their sources (external physical files, or PHP string)


Limitations
-----------

* doesn't support Zip64 archives
* doesn't support zip file comments (very rarely used, not editable in 7-Zip yet)
* needs the [Zlib extension](http://www.php.net/manual/en/book.zlib.php) only to
  compress or uncompress files in the archive (Zlib is commonly available in most
  of PHP installations)


Example
-------

```php
$zip = new clsTbsZip(); // instantiate the class

$zip->Open('archive1.zip'); // open an existing zip archive

$ok = $zip->FileExists('innerfolder/subfile1.txt'); // check if a sub-file exist in the archive

$txt = $zip->FileRead('subfile2.txt'); // retrieve the content of a sub-file

... // some work on the $txt contents

$zip->FileReplace('subfile2.txt', $txt, TBSZIP_STRING); // replace the existing sub-file

$zip->FileReplace('subfile3.txt', false); // delete the existing sub-file

$zip->FileAdd('subfile4.txt', $txt3, TBSZIP_STRING); // add a new sub-file

$zip->Flush(TBSZIP_FILE, 'archive1_new.zip'); // flush the modifications as a new archive

$zip->Close(); // close the current archive
```


Synopsis
--------

Class name: `clsTbsZip`

```php
Open($ArchFile, $UseIncludePath=false)
```
Open an original Zip archive (it can be an LibreOffice document, an MS Office
document or any other Zip archive).

Versioning:
* since TbsZip version 2.14, $ArchFile can be a PHP file handle
* argument `$UseIncludePath` is supported since TbsZip version 2.12


```php
CreateNew()
```
Create a new empty Zip archive. The new archive is virtually prepared in the PHP
memory, nothing is written at this point. Methods `FileExists()`, `FileRead()`
and `FileReplace()` cannot be used when you work on an new archive created by
`CreateNew()`, simply because the archive is empty.


Versioning:
* method `CreateNew()` is supported since TbsZip version 2.1


```php
FileExists($NameOrIdx)
```
Return `true` if the file exists in the original archive. `$NameOrIdx` must be
the full name (including  folder name inside the archive) or the index of an
existing file in the archive.


```php
FileRead($NameOrIdx, $Uncompress=true)
```
Return the contents of the file stored in the original archive. If `$Uncompress`
is `true`, then TbsZip tryies to uncompressed the contents if needed. If the
file is compressed but TbsZip cannot uncompress it then an error is raised. You
can check if the result is compressed using property `LastReadComp`.


```php
FileReplace($NameOrIdx, $Data, $DataType=TBSZIP_STRING, $Compress=true)
```
Replace or delete an existing file in the archive. Set `$Data=false` in order
to delete the file. `$DataType` accepts `TBSZIP_STRING` and `TBSZIP_FILE`
(`$Data` must then be the path of the external file to insert). `$Compress`
can be `true`, `false` or an array with keys (`'meth'`, `'len_u'`, `'crc32'`)
which means that the data is already previously compressed. *Note that the original archive is not modified, modifications will be provided as a new archive when you call the method `Flush()`.*

| Return value | Description |
|--------------|-------------|
| `false`      | if the file has not been replaced or deleted. |
| `true`       | if the file has  been successfully deleted. |
| `-1`         | if the file could not be compressed and has been stored uncompressed |
| `0`          | if the file has been stored uncompressed as expected |
| `1`          | if the file has been stored compressed has expected |
| `2`          | if the file has been stored as is with pre-compression as defined in the array `$Compress` |
  
It's very interesting to know that when you add or replace a file in the archive
with an external file (i.e. using option `TBSZIP_FILE`), then the contents of the
file is not loaded immediately in PHP memory. It will be loaded, compressed and
output on the fly and one by one when method `Flush()` is called. Thus, you can
add lot of files in an archive without occupying the PHP memory.


```php
FileAdd($Name, $Data, $DataType=TBSZIP_STRING, $Compress=true)
```
Add a new file in the archive, works like `FileReplace()`. *Note that the original
archive is not modified, modifications will be provided as a new archive when you
call the method `Flush()`.*

If `$Data` is `false` then the previously add file with the given name is canceled if any.


```php
FileCancelModif($NameOrIdx)
```
Cancel add, delete and replacements in the archive for the given file name. Return
the number of cancels.


```php
FileGetState($NameOrIdx)
```
Return a string that represents the state of the file in the archive:

| Return value | Description |
|--------------|-------------|
| `'u'`        | Unchanged   |
| `'m'`        | Modified    |
| `'d'`        | Deleted     |
| `'a'`        | Added       |
| `false`      | The file is not found in the archive  |


```php
Flush($Render=TBSZIP_DOWNLOAD, $File='', $ContentType='')
```
Actually create the new archive with all modifications. External files to be
inserted as sub-files are loaded during this proces and not before. They are
compressed and output on the fly and one by one without staying in the  PHP
memory. No temporay files are used.

| `$Render`  | Description  |
|------------|--------------|
| `TBSZIP_DOWNLOAD` | will provide the new archive as a download with HTTP headers. Use `$File` to customize the name of the downloaded file. Use `$ContentType` to customize the ContentType header. |
| `TBSZIP_DOWNLOAD + TBSZIP_NOHEADER` | will provide the new archive as a download without HTTP |
| `TBSZIP_FILE` | will provide the new archive as a physical file on the server. Use `$File` to define the path of the physical file |
| `TBSZIP_STRING` | will make method `Flush()` to return the new archive as a binary string |


```php
Debug()
```
Display information about the original archive.


```php
Close()
```
Close the opened original archive.


```php
ArchFile
```
Full path of  the opened original archive.


```php
CdFileLst
```
An array listing all existing files in the original archive with some technical
information.


```php
LastReadIdx
```
Index of the last file read by `FileRead()`. `false` means the file was not found in the archive.


```php
LastReadComp
```
Compression information about of the last file read by `FileRead()`.

* `false` means the file was not found in the archive,
* `-1` means the file was compressed and has been successfully uncompressed,
* `0` means the file was not compressed,
* `1` means the file was compressed but TbsZip was unable to uncompress.


```php
DisplayError
```
Default value is `false` until version 2.3, and `true` since version 2.4.

If the value is `true`, TbsZip error messages are displayed (using `echo`).

In any case, the last TbsZip error message is saved into property `Error`. It
can be interesting to not display error directly, for example when you flush
the new archive with the Download option, the error message may be merged to
the downloaded file.


```php
Error
```
Last error message, or `false` if no TbsZip error. Use this property in order to
check errors when property `DisplayError` is set to `false`.


Change log
----------

Version 2.16, date: 2014-04-08
* bug fix: could not download a file that has the same name as the opened archive.

Version 2.15, date:  2013-10-15
* Archives with comment can now be opened by TbsZip.
* Clearer error messages for PHP in CLI mode.
* Clearer error messages for the interconnection with OpenTBS.

Version 2.14, date:  2013-06-11
* can open an archive from a PHP file handle

Version 2.13, date: 2013-04-14
* new  method `FileGetState()`

Version 2.12, date: 2013-03-16
* bug fixed: may produce a corrupted archive when the original was using data descriptors without the signature.
* minor enhancement: new argument `$UseIncludePath`.
* minor enhancement: debug mode is smarter.

Version 2.11, date: 2011-02-14
* bug fixed: method `FileCancelModif()` doesn't cancel added files.

Version 2.10, date: 2011-08-13
* bug fixed: PHP warning *"Notice: Undefined variable: AddDataLen..."* happens when deleting a file whitout adding any file.

Version 2.9, date: 2011-07-22
* bug fixed: a minor bug on `FileRead()` when the asked file does not exists.

Version 2.8, date: 2011-06-08
* bug fixed: PHP warning *"Warning: fclose(): 10 is not a valid stream resource"* may happen when closing an archive twice.

Version 2.7, date: 2011-06-07
* bug fixed: PHP error *"supplied argument is not a valid stream resource"* or *"Undefined property: clsOpenTBS::$OutputHandle"* may happen when using method `Flush()`.

Version 2.6, date: 2011-06-07
* minor enhancement: now raise a TbsZip error if `Flush()` attempts to overwrite a locked file.

Version 2.5, date: 2011-05-12
* minor bug fixed: strict compatibility with PHP 5 (no PHP warning with error reporting E_STRICT)

Version 2.4, date: 2011-03-25
* minor bug fixed: the new created archive using `Flush()` was not unlocked at the end of the flush. The clsTbsZip instance had still an handle on it.
* minor enhancement: now raise a TbsZip error if `Flush()` attempts to overwrite the current archive.
* minor enhancement: property `DisplayError` is set to true by default.

Version 2.3, date: 2010-11-29
* minor bug fixed: an archive created with both methods `CreateNew()` and `Flush(TBSZIP_DOWNLOAD)` could be truncated because the final size of the archive was badly estimated.

Version 2.2, date: 2010-10-28
* major bug fixed: some added or modified files can be saved in the archive with a wrong CRC control code. This could make softwares to consider the file or the archive as corrupted. Only few CRC codes are wrongly saved, thus the bug is rare and can seem to appear randomly.

Version 2.1, date: 2010-07-01
* bug fixed: when adding a new file in the archive, the time of the file was wrong (date was ok)
* TbsZip now changes the date and time of a file in the archive when the file content is replaced
* new method `CreateNew()`


Contact and license
-------------------

Author: [Skrol29](http://www.tinybutstrong.com/onlyyou.html)

License: [LGPL](http://www.gnu.org/licenses/lgpl.html)
