<?php
/**
 * Created by PhpStorm.
 * User: TeleMagic
 * Date: 8/10/2016
 * Time: 1:19 PM
 */

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
class FTP
{
    private $connectionId;
    private $loginOk = false;
    private $messageArray = array();

//    const HOST = "127.0.0.1";
//    const USERNAME = 'admin';
//    const PASSWORD = 'admin';

    const HOST = "192.252.214.176";
    const USERNAME = 'afrobeat';
    const PASSWORD = '~.6vs2#X7N4mrRpEbU(t?5&|QiK*FZ]8';

    const CDN_IMAGE_DIR = "/images/";
    const CDN_AUDIO_DIR = "/audios/";
    const CDN_VIDEO_DIR = "/videos/";



    public function __construct() {

    }

    private function logMessage($message)
    {
        //echo $message . "\n";
        $this->messageArray[] = $message;
    }

    public function getMessages()
    {
        return $this->messageArray;
    }

    public function connect ($server = self::HOST, $ftpUser = self::USERNAME,
                             $ftpPassword = self::PASSWORD, $isPassive = false)
    {

        // *** Set up basic connection
        $this->connectionId = ftp_connect($server);

        // *** Login with username and password
        $loginResult = ftp_login($this->connectionId, $ftpUser, $ftpPassword);

        // *** Sets passive mode on/off (default off)
        ftp_pasv($this->connectionId, $isPassive);

        // *** Check connection
        if ((!$this->connectionId) || (!$loginResult)) {
            $this->logMessage('FTP connection has failed!');
            $this->logMessage('Attempted to connect to ' . $server . ' for user ' . $ftpUser, true);
            return false;
        } else {
            $this->logMessage('Connected to ' . $server . ', for user ' . $ftpUser);
            $this->loginOk = true;
            return true;
        }
    }

    public function makeDir($directory)
    {
        // *** If creating a directory is successful...
        if (ftp_mkdir($this->connectionId, $directory)) {

            $this->logMessage('Directory "' . $directory . '" created successfully');
            return true;

        } else {

            // *** ...Else, FAIL.
            $this->logMessage('Failed creating directory "' . $directory . '"');
            return false;
        }
    }

    private function uploadFile ($file, $fileName, $public = 'public')
    {
        // *** Set the transfer mode
//        $asciiArray = array('txt', 'csv');
//        $extension = end(explode('.', $fileFrom));
//        if (in_array($extension, $asciiArray)) {
//            $mode = FTP_ASCII;
//        } else {
//            $mode = FTP_BINARY;
//        }

        $mode = FTP_BINARY;
        ini_set('max_execution_time', 0);
        // *** Upload the file
        $s3 = Storage::disk('s3');
        $upload = $s3->put($fileName, file_get_contents($file), $public);
        // *** Check upload status
        if (!$upload) {

            $this->logMessage('CDN upload has failed!');
            //$this->disconnect();
            return false;

        } else {
            $this->logMessage('Uploaded "' . $file . '" as "' . $fileName);
            //$this->disconnect();
            return true;
        }
    }

    public function moveImageToCDN($sourceFilePath, $destinationFileName){
        $destinationFileName = self::CDN_IMAGE_DIR . $destinationFileName;

        return $this->uploadFile($sourceFilePath, $destinationFileName);
    }

    public function moveAudioToCDN($sourceFilePath, $destinationFileName){
        $destinationFileName = self::CDN_AUDIO_DIR . $destinationFileName;

        return $this->uploadFile($sourceFilePath, $destinationFileName);
    }

    public function moveVideoToCDN($sourceFilePath, $destinationFileName){
        $destinationFileName = self::CDN_VIDEO_DIR . $destinationFileName;

        return $this->uploadFile($sourceFilePath, $destinationFileName);
    }

    private function disconnect(){
        if ($this->connectionId) {
            ftp_close($this->connectionId);
        }
    }

    public function __deconstruct()
    {
        $this->logMessage('FTP Connection closed!');
        $this->disconnect();
    }
}