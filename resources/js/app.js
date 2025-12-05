import './bootstrap'
import Swal from 'sweetalert2'
window.Swal = Swal

// Jalankan QR generator hanya kalau halaman meng-set window.CANDIDATE_QR
if (window.CANDIDATE_QR) {
  import('./pages/candidate-qr')
}
