<?php

session_start();

// Bikin koneksi
$conn = mysqli_connect('localhost','root','','kasir');

//login
if(isset($_POST['login'])){
    //Initiate variable
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $check = mysqli_query($conn," SELECT * FROM user WHERE username='$username' and password='$password' ");
    $hitung = mysqli_num_rows($check);

    if($hitung>0){
        //Jika datanya ditemukan
        //berhasil login

        $_SESSION ['login'] = 'True';
        header('location:index.php');
    } else{
        //Data tidak ditemukan
        //gagal login
        echo '
        <script>alert("Username atau Password salah");
        window.location.href="login.php"
        </script>
        ';
    }
}



if(isset($_POST['tambahbarang'])){
    $namaproduk = $_POST['namaproduk'];
    $deskripsi = $_POST['deskripsi'];
    $stock = $_POST['stock'];
    $harga = $_POST['harga'];

    $tambah = mysqli_query($conn,"insert into produk(namaproduk,deskripsi,harga,stock) values ('$namaproduk','$deskripsi',
    '$harga','$stock')");

    if($tambah){
        header('location:stock.php');
    } else {
        echo '
        <script>alert("Gagal menambah barang baru");
        window.location.href="stock.php"
        </script>
        ';
    }
};

if(isset($_POST['tambahpelanggan'])){
    $namapelanggan = $_POST['namapelanggan'];
    $notelp = $_POST['notelp'];
    $alamat = $_POST['alamat'];
   

    $tambah = mysqli_query($conn,"insert into pelanggan(namapelanggan,notelp,alamat) values ('$namapelanggan','$notelp',
    '$alamat')");

    if($tambah){
        header('location:pelanggan.php');
    } else {
        echo '
        <script>alert("Gagal menambah pelanggan baru");
        window.location.href="pelanggan.php"
        </script>
        ';
    }
}

if(isset($_POST['tambahpesanan'])){
    $idpelanggan = $_POST['idpelanggan'];
   

    $tambah = mysqli_query($conn,"insert into pesanan(idpelanggan) values ('$idpelanggan')");

    if($tambah){
        header('location:index.php');
    } else {
        echo '
        <script>alert("Gagal menambah pesanan baru");
        window.location.href="index.php"
        </script>
        ';
    }
}

//produk dipilih dipesanan
if(isset($_POST['addproduk'])){
    $idproduk = $_POST['idproduk'];
    $idp = $_POST['idp'];
    $qty = $_POST['qty'];
   

    //hitung stock sekarang ada berapa
    $hitung1 = mysqli_query($conn,"select * from produk where idproduk='$idproduk'");
    $hitung2 = mysqli_fetch_array($hitung1);
    $stocksekarang = $hitung2['stock'];

    if($stocksekarang>=$qty){
       //kurangi stocknya dengan jumlah yang akan dikeluarkan
       $selisih = $stocksekarang-$qty;
       
        //stocknya ada
        $tambah = mysqli_query($conn,"insert into detailpesanan(idpesanan,idproduk,qty) values ('$idp','$idproduk','$qty')");
        $update = mysqli_query($conn,"update produk set stock='$selisih' where idproduk='$idproduk'");

    if($tambah&&$update){
        header('location:view.php?idp='.$idp);
    } else {
        echo '
        <script>alert("Gagal menambah pesanan baru");
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
    }
    } else {
        //stcok tidak cukup
        echo '
        <script>alert("Stock barang tidak ada");
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
    }
}


if(isset($_POST['barangmasuk'])){
    $idproduk = $_POST['idproduk'];
    $qty = $_POST['qty'];

    if(empty($idproduk) || empty($qty)){
        echo '
        <script>alert("Produk dan jumlah harus diisi!");
        window.location.href="masuk.php"
        </script>
        ';
    } else {
        $insertb = mysqli_query($conn,"INSERT INTO masuk (idproduk, qty) VALUES('$idproduk','$qty')");

        if($insertb){
            header('location: masuk.php');
        } else {
            echo '
            <script>alert("Gagal menambah data.");
            window.location.href="masuk.php"
            </script>
            ';
        }
    }
}

// hapus produk pesanan
if(isset($_POST['hapusprodukpesanan'])){
    $idp = $_POST['idp'];
    $idpr = $_POST['idpr'];
    $idorder = $_POST['idorder'];

    //cek qty sekarang 
    $cek1 = mysqli_query($conn,"select * from detailpesanan where iddetailpesanan='$idp'");
    $cek2 = mysqli_fetch_array($cek1);
    $qtysekarang = $cek2['qty'];

    //cek stock sekarang
    $cek3 = mysqli_query($conn,"select * from produk where idproduk='$idpr'");
    $cek4 = mysqli_fetch_array($cek3);
    $stocksekarang = $cek4['stock'];

    $hitung = $stocksekarang + $qtysekarang;

    // Update stock produk
    $update = mysqli_query($conn,"update produk set stock='$hitung' where idproduk='$idpr'");

    // Cek apakah data ada sebelum menghapus
    $cekPesanan = mysqli_query($conn, "select * from detailpesanan where idproduk='$idpr' and iddetailpesanan='$idp'");
    if (mysqli_num_rows($cekPesanan) > 0) {
        $hapus = mysqli_query($conn,"delete from detailpesanan where idproduk='$idpr' and iddetailpesanan='$idp'");
    } else {
        echo '
        <script>alert("Produk tidak ditemukan!");
        window.location.href="view.php?idp='.$idp.'"
        </script>
        ';
        exit;
    }

    // Cek apakah update dan hapus berhasil
    if($update && $hapus){
        header('location:view.php?idp='.$idorder);
    } else {
        echo '
        <script>alert("Gagal menghapus barang");
        window.location.href="view.php?idp='.$idorder.'"
        </script>
        ';
    }
}



?>