<?php
// Tampilkan data
function tampilkanSemuaData($nama_table, $andwhere= " "){
    // dokumentasi wpcodex developer
    global $wpdb;

    $table_name = $wpdb->prefix.$nama_table;
    $sql = "SELECT * FROM".$table_name."WHERE 1".$andwhere;
    $query = $wpdb->get_results($sql);

    return $query;
}

?>