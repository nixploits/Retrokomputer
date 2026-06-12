/* Types for POS Retro Komputer */

export interface User {
  id: number
  name: string
  username: string
  role: 'admin' | 'owner' | 'kasir'
  created_at?: string
  updated_at?: string
}

export interface LoginPayload {
  username: string
  password: string
}

export interface LoginResponse {
  access_token: string
  token_type: string
  user: User
}

export interface Produk {
  id: number
  kode_produk: string
  nama_produk: string
  kategori: string
  harga_beli: number
  harga_jual: number
  stok: number
  stok_minimum: number
  status: 'aktif' | 'nonaktif'
  created_at?: string
  updated_at?: string
}

export interface ProdukPayload {
  kode_produk: string
  nama_produk: string
  kategori: string
  harga_beli: number
  harga_jual: number
  stok: number
  stok_minimum: number
  status?: string
}

export interface CartItem {
  produk: Produk
  qty: number
  subtotal: number
}

export interface Transaksi {
  id: number
  kode_transaksi: string
  user_id: number
  kasir?: { id: number; name: string }
  nama_kasir?: string
  nama_pembeli?: string
  total: number
  metode_pembayaran: string
  details?: TransaksiDetail[]
  created_at: string
}

export interface TransaksiDetail {
  id: number
  transaksi_id: number
  produk_id: number
  produk?: Produk
  qty: number
  harga_satuan: number
  subtotal: number
}

export interface TransaksiPayload {
  items: {
    produk_id: number
    qty: number
  }[]
  metode_pembayaran: string
  nama_pembeli?: string
}

export interface Supplier {
  id: number
  nama: string
  telepon?: string
  alamat?: string
  created_at?: string
  updated_at?: string
}

export interface SupplierPayload {
  nama: string
  telepon?: string
  alamat?: string
}

export interface Pembelian {
  id: number
  user_id: number
  user?: { id: number; name: string }
  supplier: string
  invoice: string
  total: number
  struk?: string
  details?: PembelianDetail[]
  created_at: string
}

export interface PembelianDetail {
  id: number
  pembelian_id: number
  produk_id: number
  produk?: Produk
  qty: number
  harga_beli: number
  subtotal: number
}

export interface PembelianPayload {
  supplier: string
  invoice: string
  items: {
    produk_id: number
    qty: number
    harga_beli: number
  }[]
  struk_file?: File | null
}

export interface Retur {
  id: number
  user_id: number
  user?: { id: number; name: string }
  jenis_retur: 'penjualan' | 'pembelian'
  referensi_id: number
  pembelian?: { id: number; supplier: string }
  alasan: string
  ongkir: number
  details?: ReturDetail[]
  created_at: string
}

export interface ReturDetail {
  produk_id: number
  produk?: Produk
  qty: number
}

export interface ReturPayload {
  jenis_retur: 'penjualan' | 'pembelian'
  referensi_id: number
  alasan: string
  ongkir?: number
  items: {
    produk_id: number
    qty: number
  }[]
}

export interface RiwayatStok {
  id: number
  produk_id: number
  produk?: Produk
  tipe: 'masuk' | 'keluar'
  qty: number
  sumber: string
  referensi_id?: number
  created_at: string
}

export interface DashboardStats {
  penjualan_bulan_ini: number
  pembelian_bulan_ini: number
  laba_bersih: number
  total_transaksi: number
  kerugian_inventaris: number
}

export interface StokStats {
  total_jenis_produk: number
  stok_aman: number
  stok_rendah: number
  stok_habis: number
}

export interface FilterParams {
  search?: string
  kategori?: string
  status?: string
  tipe?: string
  page?: number
  per_page?: number
}

export interface DashboardFilterParams {
  filter_mode?: 'hari_ini' | 'minggu_ini' | 'bulan_ini' | 'harian' | 'mingguan' | 'bulanan' | 'tahunan' | 'tanggal' | 'rentang'
  filter_value?: string
  filter_year?: number
}

export interface ActiveSettings {
  logo_type: 'text' | 'image'
  logo_text: string
  logo_url: string
  logo_height: string
  bg_type: 'default' | 'color' | 'image' | 'grid'
  bg_color: string
  bg_url: string
}

export interface BarangRusak {
  id: number
  produk_id: number
  produk?: Produk
  qty: number
  kategori: 'rusak' | 'hilang'
  keterangan?: string
  bukti_foto?: string
  created_at: string
}

export interface BarangRusakPayload {
  produk_id: number
  qty: number
  kategori: 'rusak' | 'hilang'
  keterangan?: string
  bukti_foto?: string
}

export interface Notifikasi {
  id: number
  title: string
  message: string
  status_baca: boolean
  created_at: string
}

export interface ProfilKasir {
  id: number
  user_id: number
  nama: string
  kode_khusus: string
  is_active: boolean
  user?: { id: number; name: string; username: string }
  created_at?: string
  updated_at?: string
}

export interface ProfilKasirPayload {
  user_id?: number
  nama: string
  kode_khusus: string
}

/* ===== Chart Analytics Types ===== */

export interface ChartPenjualanHarian {
  tanggal: string
  total: number
  jumlah_transaksi: number
}

export interface ChartPenjualanBulanan {
  bulan: string
  bulan_num: number
  tahun: number
  total_penjualan: number
  total_pembelian: number
  laba_bersih: number
}

export interface ChartMetodePembayaran {
  metode_pembayaran: string
  jumlah_transaksi: number
  total_nominal: number
}

export interface ChartProdukTerlaris {
  nama_produk: string
  total_terjual: number
  total_pendapatan: number
  total_terjual_bulan_lalu: number
}
