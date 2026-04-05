<div class="p-6 bg-white rounded-xl shadow-md max-w-lg mx-auto mt-10 border-2 border-black shadow-[5px_5px_0px_0px_rgba(0,0,0,1)]">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Pengajuan Ijin Kunjungan</h2>
    
    <form id="formIjinKunjungan">
        @csrf
        <div class="mb-4">
            <label class="block text-sm font-bold text-gray-700 mb-2">Tanggal Ijin</label>
            <input type="date" name="tanggal" class="w-full p-2 border-2 border-black rounded-lg" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Ijin</label>
            <select name="jenis_ijin" class="w-full p-2 border-2 border-black rounded-lg" required>
                <option value="" disabled selected>-- Pilih Jenis Ijin --</option>
                <option value="Sakit">Sakit</option>
                <option value="Ijin">Ijin Keperluan Pribadi</option>
                <option value="Tugas Kantor">Tugas Kantor Luar</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>

        <div class="mb-6">
            <label class="block text-sm font-bold text-gray-700 mb-2">Alasan / Keterangan</label>
            <textarea name="alasan" rows="4" class="w-full p-2 border-2 border-black rounded-lg" placeholder="Berikan alasan singkat..." required></textarea>
        </div>

        <button type="button" onclick="submitIjin()" class="w-full bg-orange-500 hover:bg-orange-600 text-white font-bold py-3 rounded-lg border-2 border-black shadow-[3px_3px_0px_0px_rgba(0,0,0,1)] transition-all active:translate-y-1 active:shadow-none">
            Kirim Pengajuan
        </button>
    </form>
</div>

<script>
function submitIjin() {
    const form = document.getElementById('formIjinKunjungan');
    const formData = new FormData(form);

    fetch("{{ route('user.ijin.store') }}", {
        method: "POST",
        body: formData,
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: data.message,
                confirmButtonColor: '#f97316'
            }).then(() => {
                // Kembali ke dashboard setelah sukses
                window.location.reload(); 
            });
        } else {
            Swal.fire('Error', 'Terjadi kesalahan saat mengirim data', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire('Error', 'Gagal menyambung ke server', 'error');
    });
}
</script>