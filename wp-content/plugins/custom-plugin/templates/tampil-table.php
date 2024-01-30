
<style>
    table{
        width: 100%;
    }
    table th, td{
        border: 1px solid black;
    }
</style>
<h3>Tracer Study Alumni</h3>

<table class="table">
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>Alamat</th>
        <th>Aksi</th>
    </tr>
    
    <?php
    $no = 1;
    $tampil_data = tampilkanSemuaData("admin");
    foreach($tampil_data as $data):
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    ?>

    <tr>
        <td><?php echo $no++; ?></td>
        <td><?php echo $data->nama; ?></td>
        <td><?php echo $data->alamat; ?></td>
        <td>
            <a href="#" class="link button button-primary">Edit</a>
            |
            <a href="#" class="link button button-primary">Hapus</a>
        </td>
    </tr>

    <?php endforeach; ?>
</table>
