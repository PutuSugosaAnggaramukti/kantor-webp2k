<div class="page-title" style="margin-bottom: 25px;">
    <h2 style="font-size: 24px; font-weight: 800; color: #000; margin-bottom: 5px;">Data Nasabah</h2>
    <p style="font-size: 14px; font-weight: 600;">
        <span onclick="window.location.href='/admin/dashboard'" style="cursor:pointer; color:#4e4bc1;">Dashboard</span> 
        <span style="margin: 0 5px;">></span> 
        <span style="color: #007bff;">Data Nasabah</span>
    </p>
</div>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div style="display: flex; gap: 10px;">
        <button onclick="openModalExportNasabah()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; cursor: pointer;">X Export</button>
        <button onclick="openModalImportNasabah()" style="background-color: #2196F3; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; cursor: pointer;">Import Data</button>
        <button onclick="openModalFilter()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; cursor: pointer;">Filter Data</button>
        <button onclick="openModalTambahNasabah()" style="background-color: #4CAF50; color: white; border: none; padding: 8px 20px; border-radius: 12px; font-weight: 700; cursor: pointer;">+ Tambah Nasabah</button>
    </div>
    <input type="text" placeholder="Pencarian.." class="search-input" style="padding: 10px 15px; border-radius: 20px; border: 1px solid #ddd; width: 250px;">
</div>

<div id="container-nasabah">
    @include('admin.partials.nasabah_table')
</div>