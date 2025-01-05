<?php
require 'function.php';

if(isset($_SESSION['login'])){
    //udah login
}else  {
    //belum login
    header('location:login.php');
}

?>