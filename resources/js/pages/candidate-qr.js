import QRCode from "qrcode";

function $(id) {
  return document.getElementById(id);
}

function loadImage(src) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = reject;
    img.src = src;
  });
}

// untuk teks tampilan "V001" dari VB001/VM010/1/001
function formatVisitorCardCode(cardCode) {
  const s = String(cardCode ?? "").trim();
  const m = s.match(/(\d{1,3})$/);
  if (!m) return "-";
  return `V${String(m[1]).padStart(3, "0")}`;
}

// payload yang diencode ke QR -> VB001/VM001
function qrPayloadFromMeta(meta) {
  const raw = String(meta?.card_code ?? "").trim();
  return raw || "-";
}

async function buildTicketCanvas({ batchCode, meta, qrSize = 620 }) {
  const W = 900;
  const H = 1200;

  const canvas = document.createElement("canvas");
  const ctx = canvas.getContext("2d");

  canvas.width = W;
  canvas.height = H;

  // background
  ctx.fillStyle = "#ffffff";
  ctx.fillRect(0, 0, W, H);

  // logo
  let logoImg = null;
  try {
    logoImg = await loadImage("/logo-kyb.png"); 
  } catch (_) {
  }

  let y = 70;

  if (logoImg) {
    const maxLogoW = 360;
    const scale = Math.min(1, maxLogoW / logoImg.width);
    const lw = Math.floor(logoImg.width * scale);
    const lh = Math.floor(logoImg.height * scale);

    ctx.drawImage(logoImg, (W - lw) / 2, y, lw, lh);
    y += lh + 40;
  }

  // title
  ctx.fillStyle = "#0f172a";
  ctx.font = "700 34px system-ui, -apple-system, Segoe UI, Roboto, Arial";
  ctx.textAlign = "center";
  ctx.fillText("VISITOR QR", W / 2, y);
  y += 30;

  // QR
  // const payload = qrPayloadFromMeta(meta);
  const qrCanvas = document.createElement("canvas");
  await QRCode.toCanvas(qrCanvas, String(batchCode), {
    errorCorrectionLevel: "H",
    margin: 2,
    width: qrSize,
  });

  const qrX = (W - qrSize) / 2;
  const qrY = y + 35;
  ctx.drawImage(qrCanvas, qrX, qrY, qrSize, qrSize);

  y = qrY + qrSize + 55;

  // Nama | Institusi
  ctx.fillStyle = "#334155";
  ctx.font = "600 28px system-ui, -apple-system, Segoe UI, Roboto, Arial";
  const nameLine = `${meta?.full_name ?? "-"} | ${meta?.institution ?? "-"}`;
  ctx.fillText(nameLine, W / 2, y);
  y += 42;

  // Tipe + Kartu V001 (tampilan saja)
  ctx.fillStyle = "#64748b";
  ctx.font = "500 22px system-ui, -apple-system, Segoe UI, Roboto, Arial";
  const tipe = String(meta?.tipe ?? "-").toUpperCase();
  const vCode = formatVisitorCardCode(meta?.card_code);
  ctx.fillText(`Tipe: ${tipe} • Kartu: ${vCode}`, W / 2, y);

  return canvas;
}

async function renderQrToPage() {
  const meta = window.CANDIDATE_META ?? {};

  // ambil batch_code dari meta (disarankan)
  const batchCode = meta?.batch ?? window.CANDIDATE_BATCH;
  if (!batchCode) return;

  const wrap = $('qrWrap');
  if (!wrap) return;

  // Render QR kecil di halaman
  wrap.innerHTML = '';
  const qrCanvas = document.createElement('canvas');
  await QRCode.toCanvas(qrCanvas, String(batchCode), {
    errorCorrectionLevel: 'H',
    margin: 2,
    width: 220,
  });
  wrap.appendChild(qrCanvas);

  // Download handler
  const btn = $('downloadPngBtn');
  if (btn) {
    btn.addEventListener('click', async () => {
      btn.disabled = true;
      btn.classList.add('opacity-70', 'cursor-not-allowed');
      try {
        const ticket = await buildTicketCanvas({ batchCode, meta });

        const safeName = String(meta?.full_name ?? 'visitor')
          .trim()
          .replace(/\s+/g, '_')
          .replace(/[^a-zA-Z0-9_-]/g, '');

        const a = document.createElement('a');
        a.href = ticket.toDataURL('image/png');

        // nama file pakai batch_code
        a.download = `${batchCode}-${safeName || 'visitor'}.png`;

        document.body.appendChild(a);
        a.click();
        a.remove();
      } finally {
        btn.disabled = false;
        btn.classList.remove('opacity-70', 'cursor-not-allowed');
      }
    });
  }
}


document.addEventListener("DOMContentLoaded", () => {
  renderQrToPage().catch(console.error);
});
