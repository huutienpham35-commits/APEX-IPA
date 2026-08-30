<?php
declare(strict_types=1);
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__ . '/database.php';
$raw = apexReadConfig(__DIR__ . '/config.json');
$config = is_string($raw) ? json_decode($raw, true) : [];
if (!is_array($config)) $config = [];
function pageText(array $c,string $k,string $f):string{$v=trim((string)($c[$k]??''));return htmlspecialchars($v!==''?$v:$f,ENT_QUOTES,'UTF-8');}
function pageURL(array $c,string $k):string{$v=trim((string)($c[$k]??''));return filter_var($v,FILTER_VALIDATE_URL)?htmlspecialchars($v,ENT_QUOTES,'UTF-8'):'';}
$title=pageText($config,'brandTitle','APEX IPA');
$subtitle=pageText($config,'brandSubtitle','Quản lý thiết bị theo cách của bạn.');
$browserTitle=pageText($config,'browserTitle','APEX IPA');
$faviconURL=pageURL($config,'faviconURL');
$previewImages=[];
foreach((array)($config['landingPreviewImages']??[]) as $image){$url=trim((string)($image['url']??$image));if(filter_var($url,FILTER_VALIDATE_URL))$previewImages[]=['url'=>htmlspecialchars($url,ENT_QUOTES,'UTF-8'),'alt'=>pageText(is_array($image)?$image:[],'alt','Preview')];}
if(!$previewImages&&($legacyPreview=pageURL($config,'landingPreviewImageURL'))!=='')$previewImages[]=['url'=>$legacyPreview,'alt'=>'Preview'];
$getKeyURL=pageURL($config,'getKeyURL');
$ipaURL=pageURL($config,'ipaDownloadURL');
$latestVersion=trim((string)($config['latestVersion']??''));
$latestVersion=preg_replace('/[^0-9.]/','',$latestVersion)?:'1.0.0';
$pricingPlans=is_array($config['pricingPlans']??null)?$config['pricingPlans']:[];
usort($pricingPlans,static fn($a,$b)=>(int)($a['order']??999)<=>(int)($b['order']??999));
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#eeeadd">
<meta name="description" content="<?= $subtitle ?>">
<title><?= $browserTitle ?></title>
<?php if ($faviconURL !== ''): ?>
<link rel="icon" href="<?= $faviconURL ?>">
<?php endif; ?>
<link rel="preconnect" href="https://api.fontshare.com">
<link href="https://api.fontshare.com/v2/css?f[]=cabinet-grotesk@400,500,700,800,900&display=swap" rel="stylesheet">
<style>
:root{--paper:#eeeadd;--ink:#11110f;--muted:#66645d;--line:#cbc6b9;--purple:#7057ff;--acid:#d8ff52}*{box-sizing:border-box}html{background:var(--paper)}body{margin:0;color:var(--ink);background:transparent;font-family:"Cabinet Grotesk",system-ui,sans-serif;overflow-x:hidden}.site-background{position:fixed;z-index:0;inset:0;width:100%;height:100%;object-fit:cover;pointer-events:none}a{color:inherit;text-decoration:none;-webkit-tap-highlight-color:transparent}.page{position:relative;z-index:1;width:100%;overflow:hidden}.wrap{width:min(1500px,calc(100% - 48px));margin:auto}
.top{height:88px;display:grid;grid-template-columns:1fr auto;align-items:center;border-bottom:1px solid var(--line)}.wordmark{font-size:18px;font-weight:900;letter-spacing:-.04em;white-space:pre-wrap}.top-meta{justify-self:end;color:var(--muted);font-size:12px;font-weight:800;letter-spacing:.14em}
.hero{position:relative;isolation:isolate;min-height:calc(100svh - 88px);display:grid;place-items:center;padding:clamp(72px,10vw,150px) 0;text-align:center}.hero:before{content:"";position:absolute;z-index:-2;width:min(850px,78vw);aspect-ratio:1;left:50%;top:45%;translate:-50% -50%;border-radius:50%;background:radial-gradient(circle at 36% 30%,var(--acid),var(--purple) 43%,transparent 70%);filter:blur(24px);opacity:.5;animation:ambient 9s ease-in-out infinite alternate;will-change:transform}.hero:after{content:"";position:absolute;z-index:-1;inset:8% 0;background:linear-gradient(rgba(17,17,15,.06) 1px,transparent 1px),linear-gradient(90deg,rgba(17,17,15,.06) 1px,transparent 1px);background-size:54px 54px;mask-image:radial-gradient(circle,black,transparent 72%)}
.hero-core{width:100%;max-width:1180px;margin:auto}.hero-brand{margin:0 0 24px;font-size:clamp(13px,1.2vw,16px);font-weight:900;letter-spacing:.24em;text-transform:uppercase}.hero h1{margin:0;font-size:clamp(4rem,10vw,9.8rem);line-height:.78;letter-spacing:-.085em;font-weight:900;white-space:pre-wrap}.hero h1 em{display:inline-block;font-style:normal;color:var(--purple);text-shadow:4px 4px 0 var(--ink)}.hero-sub{max-width:820px;margin:40px auto 0;color:#55534c;font-size:clamp(18px,2vw,27px);line-height:1.42;font-weight:500}.hero-sub strong{color:var(--ink);font-weight:800}
.actions{display:flex;justify-content:center;gap:34px;margin-top:44px}.action{position:relative;width:min(260px,100%);min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:28px;padding:18px 22px;border:2px solid var(--ink);font-weight:900;overflow:hidden;transform:translateZ(0);transition:color .28s,box-shadow .28s,border-color .28s}.action span{position:relative;z-index:2}.action:before{content:"";position:absolute;inset:0;background:var(--ink);transform:translateY(102%);transition:transform .34s cubic-bezier(.2,.8,.2,1)}.action:hover{color:#fff;box-shadow:8px 8px 0 var(--acid)}.action:hover:before{transform:none}.action.primary{background:var(--ink);color:#fff}.action.primary:before{background:var(--purple)}.action.ipa{background:var(--purple);border-color:var(--purple);color:#fff;animation:ipaPulse 2.7s ease-out infinite}.action.ipa:before{background:var(--ink)}.action.ipa:hover{border-color:var(--ink);box-shadow:9px 9px 0 var(--acid)}.download-arrow{display:inline-block;animation:downloadArrow 1.35s ease-in-out infinite}.disabled{opacity:.38;pointer-events:none;animation:none!important}.pricing{padding:110px 0 130px;border-top:1px solid var(--line)}.pricing-head{text-align:center;margin-bottom:54px}.pricing-head span{font-size:12px;font-weight:900;letter-spacing:.2em;color:var(--purple)}.pricing-head h2{margin:12px 0 0;font-size:clamp(42px,7vw,82px);letter-spacing:-.06em}.pricing-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:22px;align-items:stretch}.price-card{display:flex;flex-direction:column;padding:34px;border:2px solid var(--ink);background:rgba(255,255,255,.42);box-shadow:8px 8px 0 var(--ink)}.price-card.featured{background:var(--ink);color:#fff;box-shadow:10px 10px 0 var(--purple)}.price-card h3{margin:0;font-size:28px}.price-value{margin:22px 0 8px;font-size:42px;font-weight:900;letter-spacing:-.05em}.price-desc{min-height:42px;color:var(--muted)}.featured .price-desc{color:#aaa}.price-features{list-style:none;padding:20px 0;margin:16px 0 28px;border-top:1px solid currentColor}.price-features li{padding:8px 0}.price-features li:before{content:"✓";margin-right:10px;color:var(--purple);font-weight:900}.price-buy{margin-top:auto;display:flex;justify-content:center;padding:16px;border:2px solid currentColor;font-weight:900}.featured .price-buy{background:var(--purple);border-color:var(--purple)}.footer{display:flex;justify-content:center;text-align:center;padding:24px 0 38px;border-top:1px solid var(--line);color:var(--muted);font-size:13px}
.top-dot{display:none}.hero-core:after{content:"\2193\A SCROLL VIEW";white-space:pre;position:absolute;left:50%;bottom:18px;translate:-50% 0;color:#fff;text-shadow:0 0 8px rgba(255,255,255,.95),0 0 20px rgba(255,255,255,.75),0 2px 5px rgba(0,0,0,.8);font-size:10px;line-height:2;font-weight:900;letter-spacing:.22em;animation:scrollCue 1.5s ease-in-out infinite}
.scroll-cue{position:absolute;left:50%;bottom:20px;translate:-50% 0;display:flex;flex-direction:column;align-items:center;gap:7px;color:var(--ink);font-size:10px;font-weight:900;letter-spacing:.22em;cursor:pointer;z-index:2}.scroll-cue svg{width:18px;height:24px;animation:scrollCue 1.5s ease-in-out infinite}.pricing{position:relative;perspective:1100px}.pricing:before{content:"";position:absolute;width:420px;height:420px;right:-180px;top:10%;border-radius:50%;background:var(--purple);filter:blur(150px);opacity:.11;pointer-events:none}.price-card{position:relative;overflow:hidden;transform-style:preserve-3d;transition:box-shadow .45s cubic-bezier(.2,.8,.2,1),border-color .3s,background .3s}.price-card:after{content:"";position:absolute;inset:-70%;background:linear-gradient(115deg,transparent 43%,rgba(255,255,255,.7) 49%,transparent 55%);transform:translateX(-38%) rotate(8deg);transition:transform .8s cubic-bezier(.2,.8,.2,1);pointer-events:none}.price-card:hover{border-color:var(--purple);box-shadow:14px 16px 0 var(--purple)}.price-card:hover:after{transform:translateX(38%) rotate(8deg)}.price-card.featured:hover{box-shadow:16px 18px 0 var(--acid)}.price-card>*{position:relative;z-index:1}.price-card h3,.price-value,.price-features li{transition:transform .35s cubic-bezier(.2,.8,.2,1),color .3s}.price-card:hover h3,.price-card:hover .price-features li{transform:translateX(5px)}.price-card:hover h3{color:var(--purple)}.price-card.featured:hover h3{color:var(--acid)}.price-card:hover .price-value{transform:scale(1.045);transform-origin:left center}.price-value{margin:14px 0 2px}.price-desc{min-height:0;margin:4px 0}.price-desc:empty{display:none}.price-features{padding:13px 0 10px;margin:8px 0 18px}.price-buy{transition:background .3s,color .3s,transform .3s}.price-buy:hover{background:var(--ink);color:#fff;transform:translateY(-3px)}.featured .price-buy:hover{background:var(--acid);border-color:var(--acid);color:var(--ink)}
/* ===== PREVIEW GALLERY WITH NAVIGATION ===== */
.preview-page{min-height:100svh;position:relative;padding:clamp(80px,10vw,130px) 24px;border-top:1px solid var(--line);overflow:hidden}
.preview-page:before{content:"";position:absolute;width:55vw;aspect-ratio:1;left:-20vw;top:8%;border-radius:50%;background:var(--acid);filter:blur(170px);opacity:.18}
.preview-title{position:relative;margin:0 0 48px;text-align:center;font:900 clamp(48px,8vw,96px)/.9 "Segoe UI",Arial,sans-serif;letter-spacing:-.06em}
.preview-gallery-wrapper{position:relative;width:min(1380px,100%);margin:auto}
.preview-gallery{display:flex;justify-content:flex-start;gap:26px;overflow-x:auto;scroll-snap-type:x mandatory;padding:12px 16px 34px;overscroll-behavior-inline:contain;scroll-behavior:smooth}
.preview-gallery::-webkit-scrollbar{display:none}
.preview-gallery{-ms-overflow-style:none;scrollbar-width:none}
.preview-frame{flex:0 0 auto;width:max-content;max-width:calc(100vw - 64px);margin:0;scroll-snap-align:center;padding:12px;border:2px solid var(--ink);background:rgba(255,255,255,.48);box-shadow:12px 14px 0 var(--purple);overflow:hidden}
.preview-frame:only-child{margin-inline:auto}
.preview-frame img{display:block;width:auto;max-width:calc(100vw - 92px);height:auto;max-height:min(66svh,720px);object-fit:contain;transition:transform .8s cubic-bezier(.2,.8,.2,1),filter .8s}
.preview-frame:hover img{transform:scale(1.025);filter:contrast(1.04)}
/* Navigation buttons - hình tròn với < > bên trong */
.preview-nav{position:absolute;top:50%;transform:translateY(-50%);z-index:10;width:52px;height:52px;padding:0;border-radius:50%;background:rgba(255,255,255,.92);border:2px solid var(--ink);color:var(--ink);font-size:0;cursor:pointer;display:grid;place-items:center;transition:all .3s cubic-bezier(.2,.8,.2,1);box-shadow:0 4px 16px rgba(0,0,0,.1);user-select:none;-webkit-tap-highlight-color:transparent}
.preview-nav svg{display:block;width:23px;height:23px;margin:0;fill:none;stroke:currentColor;stroke-width:2.6;stroke-linecap:round;stroke-linejoin:round}
.preview-nav:hover{background:var(--purple);border-color:var(--purple);color:#fff;transform:translateY(-50%) scale(1.1);box-shadow:0 8px 24px rgba(112,87,255,.35)}
.preview-nav:active{transform:translateY(-50%) scale(.92)}
.preview-nav.prev{left:-30px}
.preview-nav.next{right:-30px}
.preview-nav.hidden{opacity:0;pointer-events:none}
@media(max-width:820px){.preview-nav{width:44px;height:44px;font-size:18px}.preview-nav.prev{left:-16px}.preview-nav.next{right:-16px}}
@media(max-width:620px){.preview-nav{width:40px;height:40px;font-size:16px;background:rgba(255,255,255,.96)}.preview-nav.prev{left:-4px}.preview-nav.next{right:-4px}}
@keyframes ambient{to{transform:translate3d(4%,-3%,0) scale(1.08) rotate(8deg)}}@keyframes ipaPulse{0%,45%{box-shadow:0 0 0 0 rgba(112,87,255,.4)}75%,100%{box-shadow:0 0 0 18px rgba(112,87,255,0)}}@keyframes downloadArrow{0%,100%{transform:translateY(-3px)}50%{transform:translateY(5px)}}@keyframes scrollCue{0%,100%{transform:translateY(-3px);opacity:.55}50%{transform:translateY(5px);opacity:1}}
@keyframes priceReveal{from{opacity:.15;transform:translateY(80px) scale(.92) rotateX(8deg)}to{opacity:1;transform:translateY(0) scale(1) rotateX(0)}}@supports(animation-timeline:view()){.pricing-head,.price-card{animation:priceReveal both linear;animation-timeline:view();animation-range:entry 5% cover 36%}.price-card:nth-child(2){animation-range:entry 12% cover 42%}.price-card:nth-child(3){animation-range:entry 18% cover 48%}}
.hero,.pricing,.preview-page{scroll-snap-align:start}.pricing{min-height:100svh;display:flex;align-items:center}.pricing>.wrap{width:min(1500px,calc(100% - 48px))}.pricing-head h2{font-family:"Segoe UI",Arial,sans-serif;font-weight:800;line-height:1.08}.pricing-grid{align-items:stretch}.price-card{height:100%}@supports(animation-timeline:view()){.pricing-head,.price-card,.price-card:nth-child(2),.price-card:nth-child(3){animation-range:entry 5% cover 36%}}
@media(max-width:620px){.wrap{width:calc(100% - 24px)}.top{height:76px;grid-template-columns:1fr auto}.top-meta{display:none}.hero{min-height:calc(100svh - 76px);padding:54px 0 100px}.hero h1{font-size:clamp(3.7rem,19vw,6rem)}.hero-sub{margin-top:30px}.actions{flex-direction:column;align-items:center;gap:14px}.action{width:100%}.scroll-cue{bottom:15px}.price-card{padding:26px}.footer{flex-direction:column}}@media(prefers-reduced-motion:reduce){*,*:before,*:after{animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}
</style>
</head>
<body>
<video class="site-background" autoplay muted loop playsinline preload="auto" aria-hidden="true"><source src="https://d8j0ntlcm91z4.cloudfront.net/user_38xzZboKViGWJOttwIXH07lWA1P/hf_20260429_114316_1c7889ad-2885-410e-b493-98119fee0ddb.mp4" type="video/mp4"></video>
<main class="page">
<header class="top wrap">
<div class="wordmark"><?= $title ?></div>
<div class="top-meta">SECURE DEVICE TOOLKIT</div>
</header>
<section class="hero wrap">
<div class="hero-core">
<h1><?= $title ?></h1>
<p class="hero-sub"><strong><?= $subtitle ?></strong></p>
<div class="actions">
<a class="action primary magnetic<?= $getKeyURL===''?' disabled':'' ?>" href="<?= $getKeyURL!==''?$getKeyURL:'#' ?>" target="_blank" rel="noopener"><span>GET KEY</span><span>↗</span></a>
<a class="action ipa magnetic<?= $ipaURL===''?' disabled':'' ?>" href="<?= $ipaURL!==''?$ipaURL:'#' ?>" target="_blank" rel="noopener"><span>TẢI FILE IPA [<?= htmlspecialchars($latestVersion,ENT_QUOTES,'UTF-8') ?>]</span><span class="download-arrow">↓</span></a>
</div>
</div>
</section>
<?php if($pricingPlans): ?>
<section class="pricing">
<div class="wrap">
<div class="pricing-head">
<span>BẢNG GIÁ</span>
<h2>CHỌN GÓI PHÙ HỢP</h2>
</div>
<div class="pricing-grid">
<?php foreach($pricingPlans as $plan): $planURL=filter_var(trim((string)($plan['url']??'')),FILTER_VALIDATE_URL)?htmlspecialchars(trim((string)$plan['url']),ENT_QUOTES,'UTF-8'):'#'; ?>
<article class="price-card<?= !empty($plan['featured'])?' featured':'' ?>">
<h3><?= pageText($plan,'name','Gói dịch vụ') ?></h3>
<div class="price-value"><?= pageText($plan,'price','Liên hệ') ?></div>
<p class="price-desc"><?= pageText($plan,'description','') ?></p>
<ul class="price-features">
<?php foreach((array)($plan['features']??[]) as $feature): ?>
<li><?= htmlspecialchars((string)$feature,ENT_QUOTES,'UTF-8') ?></li>
<?php endforeach; ?>
</ul>
<a class="price-buy<?= $planURL==='#'?' disabled':'' ?>" href="<?= $planURL ?>" target="_blank" rel="noopener"><?= pageText($plan,'buttonText','MUA NGAY') ?></a>
</article>
<?php endforeach; ?>
</div>
</div>
</section>
<?php endif; ?>
<?php if($previewImages): ?>
<section class="preview-page" aria-label="Preview">
<h2 class="preview-title">PREVIEW</h2>
<div class="preview-gallery-wrapper">
<div class="preview-gallery" id="previewGallery">
<?php foreach($previewImages as $image): ?>
<figure class="preview-frame">
<img src="<?= $image['url'] ?>" alt="<?= $image['alt'] ?>" loading="lazy">
</figure>
<?php endforeach; ?>
</div>
<?php if(count($previewImages) > 1): ?>
<button class="preview-nav prev" id="prevBtn" aria-label="Ảnh trước"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg></button>
<button class="preview-nav next" id="nextBtn" aria-label="Ảnh tiếp theo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg></button>
<?php endif; ?>
</div>
</section>
<?php endif; ?>
<footer class="footer wrap">
<span>© 2026 <?= $title ?>. All rights reserved.</span>
</footer>
</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/gsap.min.js"></script>
<script>
(()=>{
// ===== PREVIEW GALLERY NAVIGATION =====
const gallery = document.getElementById('previewGallery');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

if(gallery && prevBtn && nextBtn) {
    const frames = [...gallery.querySelectorAll('.preview-frame')];
    let currentIndex = 0;
    let scrollTimer;

    const nearestIndex = () => {
        const center = gallery.scrollLeft + gallery.clientWidth / 2;
        let nearest = 0;
        let distance = Infinity;
        frames.forEach((frame, index) => {
            const frameCenter = frame.offsetLeft + frame.offsetWidth / 2;
            const nextDistance = Math.abs(frameCenter - center);
            if(nextDistance < distance) { distance = nextDistance; nearest = index; }
        });
        return nearest;
    };

    const updateButtons = () => {
        currentIndex = nearestIndex();
        prevBtn.classList.toggle('hidden', currentIndex === 0);
        nextBtn.classList.toggle('hidden', currentIndex === frames.length - 1);
    };

    const goTo = index => {
        currentIndex = Math.max(0, Math.min(frames.length - 1, index));
        const frame = frames[currentIndex];
        const target = frame.offsetLeft - (gallery.clientWidth - frame.offsetWidth) / 2;
        gallery.scrollTo({left:target,behavior:'smooth'});
        prevBtn.classList.toggle('hidden', currentIndex === 0);
        nextBtn.classList.toggle('hidden', currentIndex === frames.length - 1);
    };

    prevBtn.addEventListener('click', () => goTo(currentIndex - 1));
    nextBtn.addEventListener('click', () => goTo(currentIndex + 1));

    gallery.addEventListener('scroll', () => {
        clearTimeout(scrollTimer);
        scrollTimer = setTimeout(updateButtons, 100);
    }, {passive:true});
    window.addEventListener('resize', updateButtons);
    frames.forEach(imageFrame => imageFrame.querySelector('img')?.addEventListener('load', updateButtons, {once:true}));
    requestAnimationFrame(updateButtons);
}

if(!window.gsap||matchMedia('(prefers-reduced-motion: reduce)').matches)return;
gsap.from('.top > *',{y:-22,opacity:0,duration:.75,stagger:.08,ease:'power3.out'});
gsap.from('.hero-core > *',{y:48,opacity:0,duration:1,stagger:.1,ease:'power4.out'});
document.querySelectorAll('.magnetic').forEach(button=>{
button.addEventListener('pointermove',event=>{
const r=button.getBoundingClientRect();
gsap.to(button,{x:(event.clientX-r.left-r.width/2)*.12,y:(event.clientY-r.top-r.height/2)*.12,duration:.35,ease:'power3.out'})
});
button.addEventListener('pointerleave',()=>gsap.to(button,{x:0,y:0,duration:.6,ease:'elastic.out(1,.35)'}))
});
})();
</script>
</body>
</html>
