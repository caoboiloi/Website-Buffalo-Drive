<?php
    require_once '../function.php';
    require_once '../config.php';
    require_once './copydir.php';
    session_start();
    if (!isset($_SESSION['user']) || !isset($_SESSION['name'])) {
        header('Location: ./views/login.php');
    } else {
        $user = $_SESSION['user'];
        $name = $_SESSION['name'];
    }
    function delete_directory($dirname, $conn) {
        if (is_dir($dirname))
          $dir_handle = opendir($dirname);
        if (!$dir_handle)
            return false;
        while($file = readdir($dir_handle)) {
            if ($file != "." && $file != "..") {
                if (!is_dir($dirname."/".$file)){
                    unlink($dirname . "/" . $file);
                    delFile($dirname . "/" . $file, $conn);
                }
                        
                else{
                    delete_directory($dirname . '/' . $file, $conn);
                    delFile($dirname . "/" . $file, $conn);
                }
                        
            }
        }
        closedir($dir_handle);
        rmdir($dirname);
        return true;
    }
if (!isset($_POST['path'])) {
        die(json_encode(array('status' => false, 'data' => 'Parameters not valid')));
    }
    
    $path = $_POST['path'];

    try{
        if (!is_dir($path)){
            unlink($path);
            addFileIntoTrash($path, $user, $conn);
            delFile($path, $conn);
            
        }
        else if(delete_directory($path, $conn)){
            echo json_encode(array('status' => true, 'data' => 'delete success'));

            $namedirfile = substr($path, -(strlen($path) - strrpos($path, '/') - 1) , strlen($path) - strrpos($path, '/'));
            $usertrash = $_SERVER['DOCUMENT_ROOT'] . "/BuffaloDrive/Upload/files/trash/" . $user;
            $dest = $usertrash . '/' . $namedirfile;
            //Create folder trash for user
            if (!file_exists($usertrash)) {
                mkdir($usertrash);
            }
            if (!file_exists($dest)) {
                mkdir($dest);
            }

            addFileIntoTrash($path, $user, $conn);
            xcopy($path, $dest);
            delFile($path, $conn);
            
        }else{  
            echo json_encode(array('status' => true, 'data' => "Can't delete"));
        }
       
    }
    catch(PDOException $ex){
        die(json_encode(array('status' => false, 'data' => $ex->getMessage())));
    }

?>