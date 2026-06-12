import type { Transaksi } from '@/types'
import { customDialog } from '@/utils/dialog'

export function printReceipt(
  transaksi: Transaksi,
  uangDiterima?: number | null,
  logoText: string = 'RETRO KOMPUTER',
) {
  if (!transaksi) return

  const formatRupiah = (val: number | string): string => {
    if (val === undefined || val === null) return '0'
    return new Intl.NumberFormat('id-ID').format(Number(val))
  }

  const dateObj = new Date(transaksi.created_at)
  const formattedDate = dateObj.toLocaleDateString('id-ID', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
  const formattedTime = dateObj.toLocaleTimeString('id-ID', {
    hour: '2-digit',
    minute: '2-digit',
  })

  const kasirName = (transaksi.nama_kasir || transaksi.kasir?.name || 'KASIR').toUpperCase()
  const pembeliRow = transaksi.nama_pembeli
    ? `<div class="flex-row"><div>PELANGGAN:</div><div>${transaksi.nama_pembeli.toUpperCase()}</div></div>`
    : ''

  // Build items list HTML
  let itemsHtml = ''
  if (transaksi.details && transaksi.details.length > 0) {
    transaksi.details.forEach((item) => {
      const name = (item.produk?.nama_produk || 'BARANG').toUpperCase()
      itemsHtml += `
        <div class="item-name">${name}</div>
        <div class="item-detail">
          <div>${item.qty} x ${formatRupiah(item.harga_satuan)}</div>
          <div>${formatRupiah(item.subtotal)}</div>
        </div>
      `
    })
  } else {
    itemsHtml = '<div class="text-center font-italic">TIDAK ADA DATA ITEM</div>'
  }

  // Payment section based on cash / non-cash
  let paymentHtml = ''
  const isTunai = transaksi.metode_pembayaran.toLowerCase() === 'tunai'

  if (isTunai) {
    const cashReceived = Number(uangDiterima || transaksi.total)
    const change = Math.max(0, cashReceived - transaksi.total)
    paymentHtml = `
      <div class="flex-row">
        <div>BAYAR TUNAI</div>
        <div>${formatRupiah(cashReceived)}</div>
      </div>
      <div class="flex-row">
        <div>KEMBALIAN</div>
        <div>${formatRupiah(change)}</div>
      </div>
    `
  } else {
    paymentHtml = `
      <div class="flex-row">
        <div>DIBAYAR NONTUNAI</div>
        <div>${formatRupiah(transaksi.total)}</div>
      </div>
    `
  }

  // Standalone HTML template for print dialog
  const htmlContent = `
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="UTF-8">
      <title>STRUK - ${transaksi.kode_transaksi}</title>
      <style>
        @media print {
          @page {
            margin: 0;
            size: 58mm auto;
          }
          body {
            margin: 0;
            padding: 5px;
          }
          /* Hide print button if printed directly */
          .no-print {
            display: none !important;
          }
        }
        body {
          width: 58mm;
          max-width: 100%;
          margin: 0 auto;
          padding: 8px 4px;
          font-family: 'Courier New', Courier, monospace;
          font-size: 10px;
          line-height: 1.3;
          color: #000;
          background-color: #fff;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .title { font-size: 13px; font-weight: bold; margin-bottom: 2px; letter-spacing: 0.5px; }
        .subtitle { font-size: 9px; margin-bottom: 1px; }
        .separator { border-top: 1px dashed #000; margin: 4px 0; }
        .double-separator { border-top: 1px double #000; border-bottom: 1px double #000; height: 2px; margin: 4px 0; }
        .flex-row { display: flex; justify-content: space-between; }
        .item-name { word-wrap: break-word; overflow-wrap: break-word; }
        .item-detail { display: flex; justify-content: space-between; padding-left: 8px; margin-bottom: 3px; }
        .footer-thank { margin-top: 10px; font-size: 9px; }
        .footer-note { font-size: 8px; margin-top: 2px; }
        .no-print-container {
          padding: 10px;
          text-align: center;
          margin-bottom: 10px;
          border-bottom: 2px solid #ccc;
          background-color: #f0f0f0;
          font-family: sans-serif;
        }
        .print-btn {
          background-color: #1e40af;
          color: white;
          border: none;
          padding: 6px 12px;
          font-weight: bold;
          font-size: 12px;
          border-radius: 4px;
          cursor: pointer;
        }
        .print-btn:hover {
          background-color: #1d4ed8;
        }
      </style>
    </head>
    <body>
      <div class="no-print no-print-container">
        <button class="print-btn" onclick="window.print()">CETAK NOTA</button>
      </div>

      <div class="text-center">
        <div class="title">${logoText.toUpperCase()}</div>
        <div class="subtitle">JL. RAYA RETRO NO. 101, KOTA</div>
        <div class="subtitle">TELP: 08123456789</div>
      </div>
      <div class="separator"></div>
      <div class="flex-row">
        <div>TGL: ${formattedDate}</div>
        <div>${formattedTime}</div>
      </div>
      <div class="flex-row">
        <div>KASIR: ${kasirName}</div>
        <div>${transaksi.kode_transaksi}</div>
      </div>
      ${pembeliRow}
      <div class="separator"></div>
      
      <div class="items-list">
        ${itemsHtml}
      </div>
      
      <div class="separator"></div>
      <div class="flex-row bold">
        <div>TOTAL BELANJA</div>
        <div>Rp ${formatRupiah(transaksi.total)}</div>
      </div>
      <div class="flex-row">
        <div>METODE PEMBAYARAN</div>
        <div style="text-transform: uppercase;">${transaksi.metode_pembayaran}</div>
      </div>
      ${paymentHtml}
      
      <div class="double-separator"></div>
      <div class="text-center">
        <div class="bold footer-thank">TERIMA KASIH ATAS KUNJUNGAN ANDA</div>
        <div class="footer-note">BARANG YANG SUDAH DIBELI TIDAK DAPAT</div>
        <div class="footer-note">DITUKAR ATAU DIKEMBALIKAN</div>
      </div>
      
      <script>
        // Auto trigger browser print dialog
        window.onload = function() {
          window.print();
        }
      </script>
    </body>
    </html>
  `

  const printWindow = window.open('', '_blank', 'width=350,height=600')
  if (printWindow) {
    printWindow.document.open()
    printWindow.document.write(htmlContent)
    printWindow.document.close()
  } else {
    customDialog.warning(
      'Pop-up printer diblokir oleh browser. Harap izinkan pop-up untuk mencetak nota.',
    )
  }
}
