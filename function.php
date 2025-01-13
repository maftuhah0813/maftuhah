<?php

session_start();

// Bikin koneksi
$conn = mysqli_connect('localhost','root','','kasir');

//login
if(isset($_POST['login'])){
    //Initiate variable
    $username = $_POST['username'];
    $password = $_POST['password']; 

    $check = mysqli_query($conn," select * from user where username='$username' and password='$password' ");
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
        $insertb = mysqli_query($conn,"insert into masuk (idproduk, qty) values('$idproduk','$qty')");

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


// Edit Barang
if (isset($_POST['editbarang'])) {
    // Pastikan variabel POST terisi dengan aman
    $namaproduk =  $_POST['namaproduk'];
    $deskripsi =$_POST['deskripsi'];
    $harga = $_POST['harga'];
    $idproduk = $_POST['idproduk'];

    // Query untuk update data produk
    $query = mysqli_query($conn, "UPDATE produk SET namaproduk='$namaproduk', deskripsi='$deskripsi', harga='$harga' WHERE idproduk='$idproduk'");

    // Cek apakah query berhasil
    if ($query) {
        header('Location: stock.php');
    } else {
        echo '
            <script>
                alert("Gagal Edit");
                window.location.href="stock.php";
            </script>
        ';
    }
}

//hapus barang
if(isset($_POST['hapusbarang'])){
    $idproduk = $_POST['idproduk'];

    $query = mysqli_query($conn, "delete from produk where idproduk='$idproduk'");

    if ($query) {
        header('Location: stock.php');
    } else {
        echo '
            <script>
                alert("Gagal Edit");
                window.location.href="stock.php";
            </script>
        ';
    }
}

// Edit Pelanggan
if (isset($_POST['editpelanggan'])) {
    // Pastikan variabel POST terisi dengan aman
    $namapelanggan =  $_POST['namapelanggan'];
    $notelp =$_POST['notelp'];
    $alamat = $_POST['alamat'];
    $idpelanggan = $_POST['idpelanggan'];

    // Query untuk update data produk
    $query = mysqli_query($conn, "UPDATE pelanggan SET namapelanggan='$namapelanggan', notelp='$notelp', alamat='$alamat' WHERE idpelanggan='$idpelanggan'");

    // Cek apakah query berhasil
    if ($query) {
        header('Location: pelanggan.php');
    } else {
        echo '
            <script>
                alert("Gagal Edit");
                window.location.href="pelanggan.php";
            </script>
        ';
    }
}

//hapus pelanggan
if(isset($_POST['hapuspelanggan'])){
    $idpelanggan = $_POST['idpelanggan'];

    $query = mysqli_query($conn, "delete from pelanggan where idpelanggan='$idpelanggan'");

    if ($query) {
        header('Location: pelanggan.php');
    } else {
        echo '
            <script>
                alert("Gagal menghapus");
                window.location.href="pelanggan.php";
            </script>
        ';
    }
}

//hapus pesanan
if(isset($_POST['hapuspesanan'])){
    $idorder = $_POST['idorder'];

    $query = mysqli_query($conn, "delete from pesanan where idorder='$idorder'");

    if ($query) {
        header('Location: index.php');
    } else {
        echo '
            <script>
                alert("Gagal menghapus");
                window.location.href="index.php";
            </script>
        ';
    }
}

// Edit barang masuk
if (isset($_POST['editbarangmasuk'])) {
    $namaproduk = $_POST['namaproduk']; 
    $qty = $_POST['qty'];
    $idmasuk = $_POST['idmasuk'];

    // Query untuk update data produk
    $query = mysqli_query($conn, "UPDATE masuk SET namaproduk='$namaproduk', qty='$qty' WHERE idmasuk='$idmasuk'");

    if ($query) {
        header('Location:masuk.php');
    } else {
        echo '
            <script>
                alert("Gagal Edit:");
                window.location.href="masuk.php";
            </script>
        ';
    }
}


// Hapus Barang Masuk
if (isset($_POST['hapusbarangmasuk'])) {
    $idmasuk = $_POST['idmasuk'];

    $query = mysqli_query($conn, "DELETE FROM masuk WHERE idmasuk='$idmasuk'");

    if ($query) {
        header('Location:masuk.php');
    } else {
        echo '
            <script>
                alert("Gagal Menghapus");
                window.location.href="masuk.php";
            </script>
        ';
    }
}


?>