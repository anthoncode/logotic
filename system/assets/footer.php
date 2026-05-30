<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand">Logo<span>tic</span></div>
        <div class="footer-tagline"><?php echo $setting['footer_desc']; ?></div>
        <div class="social-links mt-3">
          <a class="social-link" href="#"><i class="fa-brands fa-facebook"></i></a>
          <a class="social-link" href="#"><i class="fa-brands fa-x-twitter"></i></a>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Explore</div>
        <a href="<?php echo $setting['website_url']; ?>/most-downloaded/" class="footer-link">Most downloaded</a>
        <a href="<?php echo $setting['website_url']; ?>/recently-added/" class="footer-link">Recently added</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Categories</div>
        <a href="<?php  echo $setting['website_url']; ?>/category/1/brand-logo/" class="footer-link">Brand Logo</a><a href="<?php  echo $setting['website_url']; ?>/category/2/logo-template/" class="footer-link">Logo Template</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Company</div>
        <a href="<?php  echo $setting['website_url']; ?>/page/4/about-us/" class="footer-link">About</a><a href="#" class="footer-link">Blog</a>
        <a href="#" class="footer-link">Careers</a><a href="<?php  echo $setting['website_url']; ?>" class="footer-link">Contact</a>
      </div>
      <div class="col-6 col-lg-2">
        <div class="footer-heading">Legal</div>
        <a href="#" class="footer-link">Privacy</a><a href="#" class="footer-link">Terms</a>
        <a href="#" class="footer-link">License</a><a href="#" class="footer-link">DMCA</a>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?php echo date("Y"); ?> <a class="footer-link-sitemap" href="<?php echo $setting['website_url']; ?>/sitemap.xml"><?php echo $setting['site_name']; ?></a> . All rights reserved.</span>
      <span>Made with <i class="bi bi-heart-fill text-danger" style="font-size:.75rem"></i> for <a class="footer-link-designer" href="https://anthoncode.com" target="_blank">anthoncode.com</a></span>
    </div>
  </div>
</footer>

<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})"><i class="bi bi-chevron-up"></i></button>

<div class="modal fade" id="logoModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Logo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="modal-logo-preview" id="modalPreview"></div>
        <div class="modal-meta" id="modalMeta"></div>
        <div class="mb-3">
          <div class="footer-heading mb-2">Download Format</div>
          <div class="download-formats" id="modalFormats"></div>
        </div>
      </div>
      <div class="modal-footer gap-2">
        <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,.07);color:var(--text-primary);border:1px solid var(--border);border-radius:10px" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-sm btn-upload" id="modalDownloadBtn"><i class="bi bi-download me-1"></i>Download SVG</button>
      </div>
    </div>
  </div>
</div>

<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="mainToast" class="toast align-items-center" role="alert">
    <div class="toast-header"><strong class="me-auto" id="toastTitle">Logotic</strong><button type="button" class="btn-close" data-bs-dismiss="toast"></button></div>
    <div class="toast-body" id="toastBody"></div>
  </div>
</div>






<!-- infinite scroll - carga post mediante ajax js -->
<script defer src="<?php echo $setting['website_url']; ?>/system/assets/js/infinite-scroll.js"></script>






<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // ── Variables globales del toast ──
const toast        = document.getElementById('downloadToast');
const ringProgress = document.getElementById('ringProgress');
const toastPercent = document.getElementById('toastPercent');
const toastSub     = document.getElementById('toastSub');
const toastTitle   = document.getElementById('toastTitle');
const toastIcon    = document.getElementById('toastIcon');
const toastClose   = document.getElementById('toastClose');

const CIRCUMFERENCE = 126;
let downloadTimer = null;   // <-- esta es la que falla si está encerrada

toastClose.addEventListener('click', () => {
    toast.classList.remove('show');
    clearInterval(downloadTimer);
});

function simulateDownload(format, downloadUrl) {
    clearInterval(downloadTimer);
    let pct = 0;

    toastTitle.textContent   = 'Downloading ' + format;
    toastPercent.textContent = '0%';
    toastSub.textContent     = 'Preparing file…';
    toastIcon.className      = 'fa-solid fa-arrow-down';
    ringProgress.style.strokeDashoffset = CIRCUMFERENCE;
    ringProgress.style.stroke = '';

    toast.classList.add('show');

    // Descarga real via iframe
    if (downloadUrl) {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = downloadUrl;
        document.body.appendChild(iframe);
        setTimeout(() => iframe.remove(), 10000);
    }

    downloadTimer = setInterval(() => {
        pct += Math.random() * 8 + 3;
        if (pct >= 100) pct = 100;

        const offset = CIRCUMFERENCE * (1 - pct / 100);
        ringProgress.style.strokeDashoffset = offset;
        toastPercent.textContent = Math.round(pct) + '%';
        toastSub.textContent = pct < 100
            ? 'Downloading… ' + Math.round(pct) + '% complete'
            : 'Download complete!';

        if (pct >= 100) {
            clearInterval(downloadTimer);
            toastIcon.className  = 'fa-solid fa-check';
            ringProgress.style.stroke = '#d4ff00';

            const el = document.getElementById('dlCountVal');
            if (el) el.textContent = parseInt(el.textContent) + 1;

            setTimeout(() => toast.classList.remove('show'), 2400);
        }
    }, 120);
}

/* ── Description truncate ── */
        (function () {
            const LIMIT = 307;
            const el = document.getElementById('itemDescription');
            const fullText = el.textContent.trim();

            if (fullText.length <= LIMIT) return;

            const truncated = fullText.slice(0, LIMIT);

            function showTruncated() {
                const link = document.createElement('a');
                link.href = '#';
                link.className = 'view-more-link';
                link.textContent = 'See more';
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    showFull();
                });
                el.innerHTML = '';
                el.appendChild(document.createTextNode(truncated + '... '));
                el.appendChild(link);
            }

            function showFull() {
                const link = document.createElement('a');
                link.href = '#';
                link.className = 'view-more-link';
                link.textContent = 'See less';
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    showTruncated();
                });
                el.innerHTML = '';
                el.appendChild(document.createTextNode(fullText + ' '));
                el.appendChild(link);
            }

            showTruncated();
        })();

  /* ── Tabs ── */
        document.querySelectorAll('.custom-tab').forEach(tab => {
            tab.addEventListener('click', function () {
                document.querySelectorAll('.custom-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
                document.getElementById(this.getAttribute('data-tab') + '-content').style.display = 'block';
            });
        });


        /* ── Size input + dynamic img tag ── */
        const BASE_URL = 'https://logotic.me/system/assets/uploads/vector-files/google-1668953057-logotic-tmpl.svg';

        const sizeInput  = document.getElementById('sizeInput');
        const urlText    = document.getElementById('urlText');
        const urlCopyBtn = document.getElementById('urlCopyBtn');
        const sizeDown   = document.getElementById('sizeDown');
        const sizeUp     = document.getElementById('sizeUp');

        function buildTag(n) {
            return `<img src="${BASE_URL}?width=${n}&height=${n}" alt="SVG Image" width="${n}" height="${n}">`;
        }

        function updateSize(val) {
            let n = parseInt(val, 10);
            if (isNaN(n)) n = 50;
            n = Math.min(1000, Math.max(10, n));
            sizeInput.value = n;
            urlText.textContent = buildTag(n);
            urlCopyBtn.innerHTML = '<i class="fa-regular fa-copy"></i>';
        }

        sizeInput.addEventListener('input',  () => updateSize(sizeInput.value));
        sizeInput.addEventListener('change', () => updateSize(sizeInput.value));
        sizeDown.addEventListener('click', () => updateSize(parseInt(sizeInput.value, 10) - 5));
        sizeUp.addEventListener('click',   () => updateSize(parseInt(sizeInput.value, 10) + 5));

        updateSize(50);

        /* ── Copy img tag ── */
        urlCopyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(urlText.textContent).then(() => {
                this.innerHTML = '<i class="fa-solid fa-check"></i>';
                this.style.background = 'var(--neon-green)';
                this.style.color = '#0a0e1a';
                setTimeout(() => {
                    this.innerHTML = '<i class="fa-regular fa-copy"></i>';
                    this.style.background = '';
                    this.style.color = '';
                }, 2000);
            });
        });

        /* ── Share crescent toggle ── */
        const shareWrapper = document.getElementById('shareWrapper');
        const shareBtn     = document.getElementById('shareBtn');

        shareBtn.addEventListener('click', e => {
            e.stopPropagation();
            shareWrapper.classList.toggle('is-open');
        });

        document.addEventListener('click', e => {
            if (!shareWrapper.contains(e.target)) shareWrapper.classList.remove('is-open');
        });

        function shareOn(platform) {
            const url   = encodeURIComponent(window.location.href);
            const title = encodeURIComponent('The Fantasy Flower illustration');
            const urls  = {
                reddit   : `https://www.reddit.com/submit?url=${url}&title=${title}`,
                twitter  : `https://twitter.com/intent/tweet?url=${url}&text=${title}`,
                pinterest: `https://pinterest.com/pin/create/button/?url=${url}&description=${title}`
            };
            window.open(urls[platform], '_blank', 'noopener,noreferrer');
            shareWrapper.classList.remove('is-open');
        }

        /* ── Copy page link ── */
        const copyLinkBtn = document.getElementById('copyLinkBtn');

        copyLinkBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(window.location.href).then(() => {
                this.classList.add('copied');
                this.querySelector('i').className = 'fa-solid fa-check';
                setTimeout(() => {
                    this.classList.remove('copied');
                    this.querySelector('i').className = 'fa-solid fa-link';
                }, 2200);
            });
        });

        /* ── Bookmark / Save ── */
        const bookmarkBtn = document.getElementById('bookmarkBtn');
        let bookmarked = false;

        bookmarkBtn.addEventListener('click', function () {
            bookmarked = !bookmarked;
            this.classList.toggle('saved', bookmarked);
            this.querySelector('i').className = bookmarked
                ? 'fa-solid fa-bookmark'
                : 'fa-regular fa-bookmark';

            this.classList.remove('bouncing');
            void this.offsetWidth;
            this.classList.add('bouncing');
            this.addEventListener('animationend', () => this.classList.remove('bouncing'), { once: true });
        });

const LOGOS=[
  {id:1,name:'Bitcoin',cat:'crypto',bg:'#f7931a',emoji:'&#8383;',downloads:1174,views:297,hot:false,formats:['SVG','PNG']},
  {id:2,name:'Chrome',cat:'tech',bg:'#fff',emoji:'&#127758;',downloads:998,views:412,hot:false,formats:['SVG','PNG','WebP']},
  {id:3,name:'Discord',cat:'social',bg:'#5865f2',emoji:'&#128172;',downloads:1118,views:318,hot:false,formats:['SVG','PNG']},
  {id:4,name:'Finder',cat:'tech',bg:'#f5f5f5',emoji:'&#128193;',downloads:876,views:203,hot:false,formats:['SVG','PNG','PDF']},
  {id:5,name:'Google',cat:'tech',bg:'#fff',emoji:'G',downloads:1186,views:393,hot:false,formats:['SVG','PNG','WebP','PDF']},
  {id:6,name:'TikTok',cat:'social',bg:'#111',emoji:'&#127925;',downloads:1180,views:303,hot:true,formats:['SVG','PNG']},
  {id:7,name:'PayPal',cat:'finance',bg:'#003087',emoji:'P',downloads:1180,views:208,hot:false,formats:['SVG','PNG']},
  {id:8,name:'Reddit',cat:'social',bg:'#ff4500',emoji:'&#129302;',downloads:875,views:324,hot:false,formats:['SVG','PNG','WebP']},
  {id:9,name:'ChatGPT',cat:'tech',bg:'#10a37f',emoji:'&#128172;',downloads:1103,views:4125,hot:true,formats:['SVG','PNG']},
  {id:10,name:'X / Twitter',cat:'social',bg:'#111',emoji:'X',downloads:980,views:2800,hot:true,formats:['SVG','PNG','WebP']},
  {id:11,name:'Threads',cat:'social',bg:'#111',emoji:'@',downloads:870,views:2100,hot:true,formats:['SVG','PNG']},
  {id:12,name:'Spotify',cat:'media',bg:'#1db954',emoji:'&#9835;',downloads:1250,views:3600,hot:true,formats:['SVG','PNG','WebP']},
  {id:13,name:'Netflix',cat:'media',bg:'#e50914',emoji:'N',downloads:1400,views:4200,hot:true,formats:['SVG','PNG','PDF']},
  {id:14,name:'Steam',cat:'gaming',bg:'#1b2838',emoji:'&#127918;',downloads:760,views:1900,hot:false,formats:['SVG','PNG']},
  {id:15,name:'Ethereum',cat:'crypto',bg:'#627eea',emoji:'&#926;',downloads:890,views:2300,hot:false,formats:['SVG','PNG','WebP']},
  {id:16,name:'Visa',cat:'finance',bg:'#1a1f71',emoji:'V',downloads:650,views:1800,hot:false,formats:['SVG','PNG','PDF']},
];
const CATEGORIES=[
  {name:'Technology',emoji:'&#128187;',count:1840,color:'#d4ff00',bg:'rgba(212,255,0,.12)'},
  {name:'Social Media',emoji:'&#128241;',count:980,color:'#38bdf8',bg:'rgba(56,189,248,.12)'},
  {name:'Finance',emoji:'&#128179;',count:620,color:'#f59e0b',bg:'rgba(245,158,11,.12)'},
  {name:'Gaming',emoji:'&#127918;',count:430,color:'#ff6b35',bg:'rgba(255,107,53,.12)'},
  {name:'Food & Drink',emoji:'&#127828;',count:370,color:'#ef4444',bg:'rgba(239,68,68,.12)'},
  {name:'Crypto',emoji:'&#8383;',count:290,color:'#f7931a',bg:'rgba(247,147,26,.12)'},
  {name:'Media',emoji:'&#127916;',count:520,color:'#ec4899',bg:'rgba(236,72,153,.12)'},
  {name:'Sports',emoji:'&#9917;',count:340,color:'#22c55e',bg:'rgba(34,197,94,.12)'},
];

let currentFilter='all';
const favs=new Set();

function isLight(hex){
  const c=hex.replace('#','');
  if(['fff','ffffff','f5f5f5'].includes(c.toLowerCase()))return true;
  const r=parseInt(c.slice(0,2),16),g=parseInt(c.slice(2,4),16),b=parseInt(c.slice(4,6),16);
  return(r*299+g*587+b*114)/1000>160;
}
function fmtDl(n){return n>=1000?(n/1000).toFixed(1)+'k':n}

/* FEATURED */
function renderFeatured(){
  const list=LOGOS.filter(l=>currentFilter==='all'||l.cat===currentFilter);
  document.getElementById('featuredGrid').innerHTML=list.map(logo=>{
    const tc=isLight(logo.bg)?'#333':'#fff';
    const fav=favs.has(logo.id);
    return `<div class="logo-row">
      <div class="cont-img" onclick="openModal(${logo.id})">
        <div class="card-logotic-logo" style="background:${logo.bg}">
          <span style="color:${tc}">${logo.emoji}</span>
        </div>
        <div class="badge-star-circle" id="fstar-${logo.id}" onclick="event.stopPropagation();toggleFav(${logo.id},this)">
          <i class="${fav?'fa-solid':'fa-regular'} fa-star"></i>
        </div>
        <div class="badge-download-pill">
          <i class="fa-solid fa-download"></i>
          <span>${fmtDl(logo.downloads)}</span>
        </div>
      </div>
      <span class="logo-label">${logo.name}</span>
    </div>`;
  }).join('');
}

/* POPULAR — same cont-img visual as Featured */
function renderPopular(){
  const list=[...LOGOS].sort((a,b)=>b.views-a.views)
    .filter(l=>currentFilter==='all'||l.cat===currentFilter).slice(0,12);
  document.getElementById('popularGrid').innerHTML=list.map((logo,i)=>{
    const tc=isLight(logo.bg)?'#333':'#fff';
    const fav=favs.has(logo.id);
    return `<div class="logo-row" style="animation:fadeInUp .4s ease ${i*45}ms both">
      <div class="cont-img" onclick="openModal(${logo.id})">
        <div class="card-logotic-logo" style="background:${logo.bg}">
          <span style="color:${tc}">${logo.emoji}</span>
        </div>
        <div class="badge-star-circle" id="pstar-${logo.id}" onclick="event.stopPropagation();toggleFav(${logo.id},this)">
          <i class="${fav?'fa-solid':'fa-regular'} fa-star"></i>
        </div>
        <div class="badge-download-pill">
          <i class="fa-solid fa-download"></i>
          <span>${fmtDl(logo.downloads)}</span>
        </div>
      </div>
      <span class="logo-label">${logo.name}</span>
    </div>`;
  }).join('');
}

/* CATEGORIES */
function renderCategories(){
  document.getElementById('categoryGrid').innerHTML=CATEGORIES.map(c=>`
    <div class="col-6 col-md-4 col-lg-3">
      <a href="#" class="category-card" onclick="filterByCategory('${c.name}',event)">
        <div class="cat-icon" style="background:${c.bg};color:${c.color}">${c.emoji}</div>
        <div><div class="cat-name">${c.name}</div><div class="cat-count">${c.count.toLocaleString()} logos</div></div>
        <i class="bi bi-chevron-right ms-auto" style="color:var(--text-muted)"></i>
      </a>
    </div>`).join('');
}

/* FILTER CHIPS — delegated event, data-cat attribute */
document.getElementById('filterChips').addEventListener('click',e=>{
  const chip=e.target.closest('.chip');
  if(!chip)return;
  document.querySelectorAll('#filterChips .chip').forEach(c=>c.classList.remove('active'));
  chip.classList.add('active');
  currentFilter=chip.dataset.cat;
  renderFeatured();renderPopular();
});

function filterByCategory(name,e){
  e.preventDefault();
  const map={'Technology':'tech','Social Media':'social','Finance':'finance','Gaming':'gaming','Food & Drink':'food','Crypto':'crypto','Media':'media','Sports':'gaming'};
  currentFilter=map[name]||'all';
  document.querySelectorAll('#filterChips .chip').forEach(c=>c.classList.toggle('active',c.dataset.cat===currentFilter));
  renderFeatured();renderPopular();
  window.scrollTo({top:400,behavior:'smooth'});
}

/* FAVOURITES */
function toggleFav(id,el){
  const icon=el.querySelector('i');
  if(favs.has(id)){favs.delete(id);icon.className='fa-regular fa-star';showToast('Removed from favourites','Info');}
  else{favs.add(id);icon.className='fa-solid fa-star';showToast('Added to favourites \u2605','Saved');}
}

/* MODAL */
function openModal(id){
  const logo=LOGOS.find(l=>l.id===id);if(!logo)return;
  const tc=isLight(logo.bg)?'#333':'#fff';
  document.getElementById('modalTitle').textContent=logo.name;
  document.getElementById('modalPreview').innerHTML=`<div style="width:130px;height:130px;background:${logo.bg};border-radius:26px;display:flex;align-items:center;justify-content:center;font-size:3.8rem;box-shadow:0 8px 32px rgba(0,0,0,.18)"><span style="color:${tc}">${logo.emoji}</span></div>`;
  document.getElementById('modalMeta').innerHTML=`<div class="meta-pill"><strong>${logo.downloads.toLocaleString()}</strong> downloads</div><div class="meta-pill"><strong>${logo.views.toLocaleString()}</strong> views</div><div class="meta-pill">Category: <strong>${logo.cat}</strong></div><div class="meta-pill">Formats: <strong>${logo.formats.join(', ')}</strong></div>`;
  document.getElementById('modalFormats').innerHTML=logo.formats.map(f=>`<button class="fmt-btn" onclick="downloadLogo('${f}','${logo.name}')">${f}</button>`).join('');
  document.getElementById('modalDownloadBtn').onclick=()=>downloadLogo('SVG',logo.name);
  new bootstrap.Modal(document.getElementById('logoModal')).show();
}

/* DOWNLOAD */
function downloadLogo(fmt,name='logo'){
  showToast(`Downloading ${name}.${fmt.toLowerCase()}\u2026`,'Download');
  setTimeout(()=>showToast(`${name}.${fmt.toLowerCase()} downloaded!`,'\u2713 Done'),1200);
}

/* SEARCH */
const heroSearch=document.getElementById('heroSearch');
const suggestions=document.getElementById('searchSuggestions');
heroSearch.addEventListener('input',()=>suggestions.classList.toggle('show',heroSearch.value.length>0));
heroSearch.addEventListener('keydown',e=>{if(e.key==='Enter')handleSearch()});
document.addEventListener('click',e=>{if(!e.target.closest('.hero-search'))suggestions.classList.remove('show')});
function handleSearch(){const q=heroSearch.value.trim();if(!q)return;suggestions.classList.remove('show');showToast(`Searching for "${q}"\u2026`,'Search');}
function selectSuggestion(val){heroSearch.value=val;suggestions.classList.remove('show');handleSearch();}

/* TOAST */
function showToast(msg,title='Logotic'){
  document.getElementById('toastBody').textContent=msg;
  document.getElementById('toastTitle').textContent=title;
  new bootstrap.Toast(document.getElementById('mainToast'),{delay:3000}).show();
}

/* SCROLL TOP */
window.addEventListener('scroll',()=>document.getElementById('scrollTop').classList.toggle('visible',window.scrollY>400));

/* STAT COUNTERS — fixed: IDs targeted directly, no nested querySelector */
function animateCounters(){
  [{id:'stat-0',target:12400,suffix:'+'},{id:'stat-1',target:3200,suffix:'+'},{id:'stat-2',target:98,suffix:'%'},{id:'stat-3',target:500,suffix:'k+'}]
  .forEach(({id,target,suffix})=>{
    const el=document.getElementById(id);
    let cur=0;const step=target/60;
    const iv=setInterval(()=>{
      cur=Math.min(cur+step,target);
      el.innerHTML=Math.round(cur).toLocaleString()+`<span class="stat-suffix">${suffix}</span>`;
      if(cur>=target)clearInterval(iv);
    },22);
  });
}

/* INIT */
document.addEventListener('DOMContentLoaded',()=>{
  renderFeatured();renderPopular();renderCategories();
  setTimeout(animateCounters,400);
});



</script>
</body>

</html>