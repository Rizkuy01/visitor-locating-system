import QRCode from "qrcode";

function ready(fn) {
  if (document.readyState !== "loading") fn();
  else document.addEventListener("DOMContentLoaded", fn);
}

ready(async () => {
  const el = document.getElementById("qrWrap");
  if (!el) return;

  const value = window.CANDIDATE_QR;
  if (!value) return;

  // bersihin dulu
  el.innerHTML = "";

  try {
    // render ke <canvas> biar simple dan responsif
    const canvas = document.createElement("canvas");
    canvas.className = "max-w-[240px] w-full h-auto";
    el.appendChild(canvas);

    await QRCode.toCanvas(canvas, String(value), {
      errorCorrectionLevel: "M",
      margin: 1,
      width: 240,
    });
  } catch (err) {
    console.error("QR render failed:", err);
    el.innerHTML =
      '<div class="text-sm text-red-600">Gagal membuat QR. (lihat console)</div>';
  }
});
