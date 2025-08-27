<script>
function showToast(message, type="success"){
  const container=document.getElementById("toastContainer");
  const toast=document.createElement("div");
  toast.className="custom-toast mb-2";
  const color=type==="success"?"#28a745":"#e02525ff";
  toast.style.borderColor=color; toast.style.color=color;
  toast.innerHTML=`<div class="p-2 fw-bold">${type==="success"?"✅ Success":"❌ Error"}</div>
                   <div class="px-2 pb-2">${message}</div>
                   <div class="toast-progress"><div class="toast-progress-bar" style="background:${color}"></div></div>`;
  container.appendChild(toast);
  const progress=toast.querySelector(".toast-progress-bar");
  setTimeout(()=>{progress.style.width="0%";},50);
  setTimeout(()=>{
    toast.style.opacity="0";
    toast.style.transform="translateX(100px)";
    setTimeout(()=>toast.remove(),300);
  },2000);
}
</script>

<script>
const toastMsg = <?php echo json_encode($toastMsg); ?>;
const toastType = <?php echo json_encode($toastType); ?>;
if (toastMsg) { showToast(toastMsg, toastType || "success"); }
</script>
<style>
    *{box-sizing:border-box} input{outline:0}
    #toastContainer{position:fixed;top:20px;right:20px;z-index:2000;display:flex;flex-direction:column;align-items:flex-end}
    .custom-toast{min-width:250px;background:#fff;border-left:5px solid;border-radius:0px;box-shadow:0 4px 10px rgba(0,0,0,.15);overflow:hidden;transition:all .3s ease}
    .toast-progress{height:4px;background:rgba(0,0,0,.1);width:100%;overflow:hidden}
    .toast-progress-bar{height:100%;width:100%;transition:width 2s linear}
  </style>