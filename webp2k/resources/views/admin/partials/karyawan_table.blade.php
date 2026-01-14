<div class="page-header">
    <h2 style="font-weight: 700;">Data Karyawan</h2>
    <p>Dashboard > <span style="color: #3b82f6;">Data Karyawan</span></p>
</div>

<div class="table-container" style="background: white; padding: 20px; border-radius: 15px;">
    <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <thead>
            <tr style="text-align: center;">
                <th style="border: 1px solid #000; padding: 15px;">No</th>
                <th style="border: 1px solid #000; padding: 15px;">Kode AO</th>
                <th style="border: 1px solid #000; padding: 15px;">Nama</th>
                <th style="border: 1px solid #000; padding: 15px;">Option</th>
            </tr>
        </thead>
        <tbody style="font-weight: 700; text-align: center;">
            <tr>
                <td style="border: 1px solid #000; padding: 15px;">1</td>
                <td style="border: 1px solid #000; padding: 15px;">PG.803</td>
                <td style="border: 1px solid #000; padding: 15px; text-align: left;">WAHYU</td>
                <td style="border: 1px solid #000; padding: 15px;">
                    <div style="display: flex; justify-content: center; gap: 10px;">
                        <button class="btn-action-edit" onclick="openEditModal('PG.803', 'WAHYU')" 
                                style="background-color: #8e94a9; color: white; border: none; padding: 8px 10px; border-radius: 8px; cursor: pointer;">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </button>
                        
                        <button class="btn-action-info" onclick="openDetailModal()"
                                style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid #333; background: white; font-weight: bold; cursor: pointer;">
                            i
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>