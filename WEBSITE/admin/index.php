<?php
declare(strict_types=1);
// Admin panel — require PHP host. Login password = ADMIN_PASSWORD in api.php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
?><!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>APEX Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800;900&family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <style>
    :root{
      color-scheme:dark;
      --bg:#070814;--card:#13172a;--line:#2a3152;--line2:#3a4270;
      --text:#f4f5ff;--muted:#96a0c8;--accent:#8b5cf6;--accent2:#3b82f6;
      --ok:#22c55e;--danger:#ef4444;--warn:#f59e0b;--sidebar:260px;
      --radius:16px;--shadow:0 18px 50px rgba(0,0,0,.35);
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;font:15px/1.45 Geist,system-ui,-apple-system,Segoe UI,sans-serif;color:var(--text);
      background:radial-gradient(900px 420px at 10% -10%,rgba(139,92,246,.22),transparent 55%),
                 radial-gradient(700px 380px at 100% 0%,rgba(59,130,246,.16),transparent 50%),var(--bg);
    }
    button,input,textarea,select{font:inherit}button{cursor:pointer}
    .hidden{display:none !important}
    #loginScreen{min-height:100%;display:grid;place-items:center;padding:24px}
    .login-card{
      width:min(420px,100%);background:linear-gradient(180deg,rgba(24,29,53,.95),rgba(13,16,32,.98));
      border:1px solid var(--line);border-radius:22px;padding:28px 24px 22px;box-shadow:var(--shadow);
    }
    .brand{display:flex;align-items:center;gap:12px;margin-bottom:18px}
    .logo{
      width:46px;height:46px;border-radius:14px;display:grid;place-items:center;font-weight:800;font-size:18px;
      background:linear-gradient(135deg,var(--accent),var(--accent2));box-shadow:0 10px 30px rgba(139,92,246,.35);
    }
    .brand h1{margin:0;font-size:22px}.brand p{margin:2px 0 0;color:var(--muted);font-size:13px}
    .field{margin:12px 0}
    .field label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600}
    .field input,.field textarea,.field select{
      width:100%;border:1px solid var(--line);background:#0a0d1a;color:var(--text);
      border-radius:12px;padding:12px 14px;outline:none;
    }
    .field input:focus,.field textarea:focus,.field select:focus{
      border-color:#6d5efc;box-shadow:0 0 0 3px rgba(139,92,246,.18);
    }
    .field textarea{min-height:120px;resize:vertical}
    .field .hint{font-size:12px;color:var(--muted);margin-top:6px}
    .check{display:flex;align-items:center;gap:10px;padding:10px 0}
    .check input{width:18px;height:18px}
    .btn{border:0;border-radius:12px;padding:11px 16px;font-weight:700;color:white;background:#2a3152}
    .btn:hover{filter:brightness(1.08)}.btn:disabled{opacity:.55;cursor:not-allowed}
    .btn-primary{background:linear-gradient(90deg,#7c3aed,#2563eb);width:100%}
    .btn-accent{background:linear-gradient(90deg,#7c3aed,#2563eb)}
    .btn-ghost{background:transparent;border:1px solid var(--line);color:var(--text)}
    .btn-danger{background:linear-gradient(90deg,#b91c1c,#ef4444)}
    .btn-sm{padding:8px 12px;font-size:13px;border-radius:10px}
    .login-error{color:#fecaca;background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.35);padding:10px 12px;border-radius:10px;margin-top:12px;font-size:13px;display:none}
    .login-hint{margin-top:14px;color:var(--muted);font-size:12px;text-align:center}
    #app{min-height:100%;display:grid;grid-template-columns:var(--sidebar) 1fr}
    .sidebar{
      position:sticky;top:0;height:100vh;border-right:1px solid var(--line);
      background:linear-gradient(180deg,rgba(16,20,40,.96),rgba(8,10,20,.98));
      padding:18px 14px;display:flex;flex-direction:column;gap:8px;
    }
    .side-brand{display:flex;align-items:center;gap:10px;padding:8px 10px 16px;border-bottom:1px solid var(--line);margin-bottom:8px}
    .side-brand .logo{width:38px;height:38px;font-size:14px;border-radius:12px}
    .side-brand strong{display:block;font-size:14px}.side-brand span{display:block;font-size:11px;color:var(--muted)}
    .nav-btn{
      width:100%;text-align:left;display:flex;align-items:center;gap:10px;border:1px solid transparent;
      background:transparent;color:var(--muted);border-radius:12px;padding:12px;font-weight:700;
    }
    .nav-btn:hover{background:rgba(255,255,255,.04);color:var(--text)}
    .nav-btn.active{
      color:white;background:linear-gradient(90deg,rgba(124,58,237,.28),rgba(37,99,235,.18));
      border-color:rgba(139,92,246,.35);
    }
    .nav-ico{width:28px;height:28px;border-radius:9px;display:grid;place-items:center;background:rgba(255,255,255,.06);font-size:14px}
    .side-foot{margin-top:auto;padding-top:12px;border-top:1px solid var(--line);display:grid;gap:8px}
    .side-foot .meta{font-size:11px;color:var(--muted);padding:0 8px}
    .main{min-width:0;display:flex;flex-direction:column;min-height:100vh}
    .topbar{
      position:sticky;top:0;z-index:5;display:flex;align-items:center;justify-content:space-between;gap:12px;
      padding:16px 22px;background:rgba(7,8,20,.72);backdrop-filter:blur(14px);border-bottom:1px solid rgba(42,49,82,.8);
    }
    .topbar h2{margin:0;font-size:20px}.topbar p{margin:2px 0 0;color:var(--muted);font-size:13px}
    .top-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .content{padding:20px 22px 40px;max-width:1100px;width:100%}
    .page{display:none}.page.active{display:block}
    .card{
      background:linear-gradient(180deg,rgba(24,29,53,.9),rgba(16,20,40,.95));
      border:1px solid var(--line);border-radius:var(--radius);padding:18px;margin-bottom:14px;
      box-shadow:0 10px 30px rgba(0,0,0,.18);
    }
    .card h3{margin:0 0 4px;font-size:16px}.card .sub{color:var(--muted);font-size:13px;margin:0 0 14px}
    .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .row-actions{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
    .spacer{flex:1}
    .pill{
      display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;
      background:rgba(139,92,246,.15);color:#d8b4fe;border:1px solid rgba(139,92,246,.3);
    }
    .pill.ok{background:rgba(34,197,94,.12);color:#86efac;border-color:rgba(34,197,94,.3)}
    .pill.warn{background:rgba(245,158,11,.12);color:#fcd34d;border-color:rgba(245,158,11,.3)}
    .game-head,.item-head{display:flex;align-items:center;gap:10px;margin-bottom:12px}
    .game-block{border:1px solid var(--line);border-radius:14px;padding:14px;background:rgba(10,13,26,.45);margin-top:12px}
    .item-block{border-top:1px dashed var(--line2);padding-top:14px;margin-top:14px}
    .item-block:first-child{border-top:0;padding-top:0;margin-top:0}
    .file-line{font-size:12px;color:var(--muted);word-break:break-all;margin-top:4px}
    .empty{border:1px dashed var(--line2);border-radius:14px;padding:28px;text-align:center;color:var(--muted);background:rgba(255,255,255,.02)}
    .stats{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:14px}
    .stat{background:rgba(255,255,255,.03);border:1px solid var(--line);border-radius:14px;padding:14px}
    .stat b{display:block;font-size:22px;margin-top:4px}.stat span{color:var(--muted);font-size:12px;font-weight:600}
    #toast{
      position:fixed;right:18px;bottom:18px;z-index:50;min-width:220px;max-width:min(420px,calc(100vw - 36px));
      background:#151a33;border:1px solid var(--line);color:white;padding:12px 14px;border-radius:12px;
      box-shadow:var(--shadow);display:none;font-size:13px;font-weight:600;
    }
    #toast.err{border-color:rgba(239,68,68,.5);background:#2a1220}
    #toast.ok{border-color:rgba(34,197,94,.45);background:#0f241a}
    .mobile-toggle{display:none}
    @media (max-width:900px){:root{--sidebar:230px}.grid-2,.stats{grid-template-columns:1fr}}
    @media (max-width:760px){
      #app{grid-template-columns:1fr}
      .sidebar{position:fixed;inset:0 auto 0 0;width:min(280px,86vw);z-index:20;transform:translateX(-105%);transition:transform .2s ease}
      .sidebar.open{transform:translateX(0)}
      .mobile-toggle{display:inline-flex}
      .backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:15;display:none}
      .backdrop.show{display:block}
      .content,.topbar{padding-left:16px;padding-right:16px}
    }
    body:before,body:after{content:"";position:fixed;z-index:-1;pointer-events:none;filter:blur(80px);border-radius:50%;opacity:.5}
    body:before{width:42vw;height:42vw;left:-12vw;top:20vh;background:#5b21b6;animation:orbA 14s ease-in-out infinite alternate}
    body:after{width:38vw;height:38vw;right:-10vw;bottom:-12vw;background:#075985;animation:orbB 17s ease-in-out infinite alternate}
    @keyframes orbA{to{transform:translate(16vw,-8vh) scale(.78)}}@keyframes orbB{to{transform:translate(-14vw,-18vh) scale(1.18)}}
    #loginScreen{background-image:linear-gradient(rgba(7,8,20,.75),rgba(7,8,20,.92)),url('https://picsum.photos/seed/cyber-control/1920/1080');background-size:cover;background-position:center}
    .login-card{position:relative;overflow:hidden;padding:38px;border-radius:30px;border-color:rgba(167,139,250,.34);backdrop-filter:blur(24px);box-shadow:0 40px 100px rgba(0,0,0,.6),0 0 80px rgba(124,58,237,.16)}
    .login-card:before{content:"";position:absolute;width:220px;height:220px;right:-100px;top:-120px;background:radial-gradient(circle,rgba(59,130,246,.5),transparent 68%)}
    .logo{font-size:0;background:linear-gradient(135deg,#a855f7,#2563eb 58%,#22d3ee);box-shadow:0 0 30px rgba(139,92,246,.45)}.logo i{font-size:18px}
    #app{grid-template-columns:286px 1fr;gap:20px;padding:18px;max-width:1680px;margin:auto}
    .sidebar{height:calc(100vh - 36px);top:18px;border:1px solid rgba(139,92,246,.22);border-radius:28px;padding:18px;background:linear-gradient(160deg,rgba(20,21,48,.9),rgba(7,8,22,.94));backdrop-filter:blur(28px);box-shadow:0 28px 70px rgba(0,0,0,.38)}
    .nav-btn{position:relative;overflow:hidden;padding:13px 14px;border-radius:14px;transition:transform .35s cubic-bezier(.2,.8,.2,1),background .35s,color .35s}.nav-btn:hover{transform:translateX(5px)}
    .nav-btn.active:after{content:"";position:absolute;left:0;top:22%;height:56%;width:3px;border-radius:9px;background:#22d3ee;box-shadow:0 0 14px #22d3ee}
    .nav-ico{font-size:13px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.06)}
    .main{border:1px solid rgba(139,92,246,.15);border-radius:28px;overflow:hidden;background:rgba(5,7,19,.44);backdrop-filter:blur(10px)}
    .topbar{padding:22px 28px;background:rgba(8,10,27,.72)}.topbar h2{font-size:25px;font-weight:800;letter-spacing:-.02em}
    .content{max-width:1240px;padding:32px 34px 120px}.page.active{animation:pageIn .6s cubic-bezier(.2,.8,.2,1)}@keyframes pageIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
    .stats{grid-template-columns:repeat(12,1fr);grid-auto-flow:dense;gap:0;border-radius:24px;overflow:hidden;border:1px solid rgba(139,92,246,.2);margin-bottom:24px}.stat{grid-column:span 4;border:0;border-right:1px solid var(--line);border-radius:0;padding:22px 24px;background:linear-gradient(145deg,rgba(31,35,70,.78),rgba(14,18,40,.78))}.stat:last-child{border-right:0}.stat b{font-size:27px}
    .card{position:relative;overflow:hidden;border-radius:24px;padding:24px;margin-bottom:20px;background:linear-gradient(145deg,rgba(25,29,61,.84),rgba(10,13,32,.92));border-color:rgba(139,92,246,.18);box-shadow:0 18px 45px rgba(0,0,0,.2);transition:transform .5s cubic-bezier(.2,.8,.2,1),border-color .5s,box-shadow .5s}.card:hover{transform:translateY(-4px);border-color:rgba(139,92,246,.4);box-shadow:0 28px 70px rgba(0,0,0,.34),0 0 40px rgba(124,58,237,.09)}
    .card h3{font-size:20px;font-weight:800}.grid-2{gap:16px}.field input,.field textarea,.field select{padding:13px 15px;border-radius:13px;background:rgba(5,7,20,.72);transition:border .25s,transform .25s,box-shadow .25s}.field input:hover,.field textarea:hover,.field select:hover{border-color:rgba(139,92,246,.48)}
    .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;transition:transform .3s cubic-bezier(.2,.8,.2,1),filter .3s,box-shadow .3s}.btn:hover{transform:translateY(-2px);box-shadow:0 12px 26px rgba(0,0,0,.24)}.btn:active{transform:scale(.97)}
    .btn-accent,.btn-primary{background:linear-gradient(100deg,#9333ea,#4f46e5 48%,#0284c7);background-size:180% 100%;animation:gradientMove 5s ease infinite alternate}@keyframes gradientMove{to{background-position:100% 0}}
    .game-block{border-radius:20px;padding:18px;background:linear-gradient(145deg,rgba(17,21,47,.8),rgba(7,10,27,.86));border-color:rgba(96,165,250,.18);transition:transform .45s,border-color .45s}.game-block:hover{transform:translateY(-3px);border-color:rgba(168,85,247,.42)}
    .pill{backdrop-filter:blur(12px)}#toast{border-radius:16px;backdrop-filter:blur(20px)}
    @media(max-width:900px){#app{grid-template-columns:238px 1fr;padding:10px;gap:10px}.sidebar{height:calc(100vh - 20px);top:10px}.stats{grid-template-columns:1fr}.stat{grid-column:auto;border-right:0;border-bottom:1px solid var(--line)}}
    @media(max-width:760px){#app{display:block;padding:0}.main{border:0;border-radius:0}.sidebar{top:0;height:100vh;border-radius:0}.content{padding:22px 16px 100px}.topbar{padding:17px 16px}.stats{display:grid}.card{padding:18px}}
    /* Stable responsive shell: these rules intentionally override the legacy breakpoints above. */
    html,body{overflow-x:hidden;width:100%}
    #app{width:100%;min-width:0;align-items:start}
    .sidebar{overflow-y:auto;overscroll-behavior:contain;transform:none}
    .main{width:100%;max-width:100%;overflow:visible}
    .content{margin:0 auto;min-width:0}
    .topbar>div:first-child{min-width:0}.topbar h2,.topbar p{white-space:normal}.top-actions{flex-shrink:0}
    .catalog-group{border:1px solid rgba(96,165,250,.2);border-radius:20px;background:rgba(8,11,29,.68);margin:12px 0;overflow:hidden}
    .catalog-group>summary,.catalog-item>summary{list-style:none;cursor:pointer}.catalog-group>summary::-webkit-details-marker,.catalog-item>summary::-webkit-details-marker{display:none}
    .catalog-summary{display:flex;align-items:center;gap:12px;padding:17px 18px;transition:background .25s}.catalog-summary:hover{background:rgba(139,92,246,.08)}
    .catalog-summary .summary-icon{width:42px;height:42px;border-radius:13px;display:grid;place-items:center;background:linear-gradient(135deg,rgba(147,51,234,.25),rgba(2,132,199,.2));color:#c4b5fd}
    .catalog-summary .chevron{transition:transform .25s;color:var(--muted)}.catalog-group[open]>.catalog-summary .chevron,.catalog-item[open]>summary .chevron{transform:rotate(180deg)}
    .catalog-body{padding:0 14px 14px}.catalog-item{border-top:1px solid rgba(255,255,255,.07)}
    .catalog-item>summary{display:flex;align-items:center;gap:10px;padding:13px 4px}.catalog-item-title{min-width:0;flex:1}.catalog-item-title strong{display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.catalog-item-title small{display:block;color:var(--muted);margin-top:2px}
    .catalog-editor{padding:2px 4px 16px}.catalog-editor .grid-2{background:rgba(0,0,0,.12);padding:14px;border-radius:16px}
    .media-preview{position:relative;min-height:210px;border-radius:20px;overflow:hidden;border:1px solid rgba(139,92,246,.28);background:linear-gradient(145deg,#0b1027,#101733);display:grid;place-items:center;margin-top:16px}
    .media-preview img,.media-preview video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.media-preview:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent,rgba(4,6,18,.64));pointer-events:none}.media-preview-empty{position:relative;z-index:1;color:var(--muted);text-align:center}.media-preview-empty i{display:block;font-size:30px;margin-bottom:10px;color:#a78bfa}
    .visibility-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));grid-auto-flow:dense;gap:10px}
    .visibility-option{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:11px;min-width:0;padding:14px;border:1px solid var(--line);border-radius:16px;background:rgba(5,7,20,.55);cursor:pointer;transition:transform .3s,border-color .3s,background .3s}
    .visibility-option:hover{transform:translateY(-2px);border-color:rgba(139,92,246,.48);background:rgba(124,58,237,.08)}.visibility-option>span:nth-child(2){min-width:0}.visibility-option strong,.visibility-option small{display:block}.visibility-option small{color:var(--muted);font-size:11px;line-height:1.25;margin-top:3px}.visibility-option input{width:20px;height:20px;accent-color:#8b5cf6}.visibility-icon{width:36px;height:36px;display:grid;place-items:center;border-radius:11px;background:linear-gradient(135deg,rgba(147,51,234,.2),rgba(2,132,199,.18));color:#c4b5fd}
    @media(max-width:760px){
      #app{width:100%;min-height:100dvh}.sidebar{position:fixed;inset:0 auto 0 0!important;width:min(300px,88vw)!important;height:100dvh!important;border-radius:0 24px 24px 0!important;padding:18px!important;transform:translate3d(-105%,0,0)!important;transition:transform .28s cubic-bezier(.2,.8,.2,1)!important;z-index:100!important}
      .sidebar.open{transform:translate3d(0,0,0)!important}.backdrop{z-index:90!important;backdrop-filter:blur(5px)}
      .main{width:100%;min-height:100dvh}.topbar{width:100%;align-items:center;gap:8px}.topbar p{display:none}.top-actions{gap:6px}.top-actions .pill{display:none}.top-actions .btn{padding:10px 12px;font-size:12px}
      .mobile-toggle{display:inline-flex!important;min-width:40px}.content{width:100%;padding:20px 14px 96px}.grid-2{grid-template-columns:minmax(0,1fr)!important}.card{width:100%;overflow:hidden}
      .game-head,.item-head,.row-actions{align-items:flex-start}.field input,.field textarea,.field select{font-size:16px}.catalog-summary{padding:14px}.catalog-editor .grid-2{padding:10px}.catalog-summary .pill{display:none}
      .visibility-grid{grid-template-columns:minmax(0,1fr)}
    }
    /* Editorial workspace: restrained surfaces, compact information and clear hierarchy. */
    .editorial-admin{color-scheme:light;--bg:#f1f0eb;--card:#fff;--line:#d8d7d1;--line2:#bbb9b1;--text:#171714;--muted:#74736d;--accent:#2251ff;--danger:#c5332b;background:#f1f0eb;color:var(--text)}
    .editorial-admin:before,.editorial-admin:after{display:none}
    .editorial-admin #loginScreen{background:#e9e8e1;background-image:none}
    .editorial-admin .login-card{background:#fff;border:1px solid #cbc9c1;border-radius:10px;box-shadow:0 24px 70px rgba(20,20,16,.12);backdrop-filter:none}
    .editorial-admin .login-card:before{display:none}
    .editorial-admin #app{grid-template-columns:250px 1fr;gap:0;padding:0;max-width:none}
    .editorial-admin .sidebar{height:100vh;top:0;border:0;border-right:1px solid #292923;border-radius:0;padding:22px 16px;background:#191916;color:#f7f6f0;box-shadow:none;backdrop-filter:none}
    .editorial-admin .side-brand{padding:2px 8px 22px;border-bottom:1px solid #393933;margin-bottom:14px}
    .editorial-admin .logo{background:#2251ff;box-shadow:none;border-radius:7px}
    .editorial-admin .nav-btn{border-radius:7px;color:#aaa9a2;padding:11px 10px;transition:background .16s,color .16s;overflow:visible}
    .editorial-admin .nav-btn:hover{transform:none;background:#292925;color:#fff}
    .editorial-admin .nav-btn.active{background:#f4f3ed;color:#171714}
    .editorial-admin .nav-btn.active:after{display:none}
    .editorial-admin .nav-ico{background:transparent;border:0;color:inherit}
    .editorial-admin .main{border:0;border-radius:0;background:#f1f0eb;backdrop-filter:none}
    .editorial-admin .topbar{padding:23px 30px;background:rgba(241,240,235,.94);border-bottom:1px solid #d0cfc8;backdrop-filter:blur(12px)}
    .editorial-admin .topbar h2{font-size:27px;letter-spacing:-.035em}
    .editorial-admin .content{max-width:1180px;padding:34px 30px 100px}
    .editorial-admin .stats{border:1px solid #d8d7d1;border-radius:8px;box-shadow:none}
    .editorial-admin .stat{background:#e8e7e1;border-color:#d1d0c9;color:#171714}
    .editorial-admin .card{background:#fff;border:1px solid #d8d7d1;border-radius:9px;padding:22px;box-shadow:none;transition:border-color .15s;margin-bottom:16px}
    .editorial-admin .card:hover{transform:none;border-color:#aaa89f;box-shadow:none}
    .editorial-admin .card h3{font-size:18px;letter-spacing:-.02em}
    .editorial-admin .field input,.editorial-admin .field textarea,.editorial-admin .field select{background:#faf9f5;color:#171714;border:1px solid #cfcec7;border-radius:6px;box-shadow:none}
    .editorial-admin .field input:focus,.editorial-admin .field textarea:focus,.editorial-admin .field select:focus{border-color:#2251ff;box-shadow:0 0 0 3px rgba(34,81,255,.1)}
    .editorial-admin .btn{border-radius:6px;box-shadow:none;animation:none}
    .editorial-admin .btn:hover{transform:none;box-shadow:none;filter:brightness(.96)}
    .editorial-admin .btn-accent,.editorial-admin .btn-primary{background:#2251ff;color:#fff;animation:none}
    .editorial-admin .btn-ghost{background:transparent;color:inherit;border-color:#4b4b45}
    .editorial-admin .btn-danger{background:#fff2f0;color:#a42922;border-color:#e1b4af}
    .editorial-admin .pill{background:#efeee8;color:#55544e;border:1px solid #d5d3cc;border-radius:5px;backdrop-filter:none}
    .editorial-admin .game-block,.editorial-admin .catalog-group{background:#faf9f5;border:1px solid #d8d7d1;border-radius:7px;box-shadow:none}
    .editorial-admin .game-block:hover{transform:none;border-color:#aaa89f}
    .editorial-admin .catalog-summary:hover{background:#efeee8}
    .editorial-admin .catalog-summary .summary-icon,.editorial-admin .visibility-icon{background:#e8ebff;color:#2251ff;border-radius:6px}
    .editorial-admin .catalog-item{border-color:#dfded8}.editorial-admin .catalog-editor .grid-2{background:#f0efe9;border-radius:6px}
    .editorial-admin .visibility-option{background:#faf9f5;border-radius:7px}.editorial-admin .visibility-option:hover{transform:none;background:#f2f1eb;border-color:#aaa89f}
    .editorial-admin .media-preview{background:#dddcd5;border-color:#c8c7c0;border-radius:7px}
    .editorial-admin .media-preview:after{display:none}
    .link-list{display:grid;gap:0;margin-top:18px;border-top:1px solid var(--line)}
    .link-editor{display:grid;grid-template-columns:44px minmax(0,1fr) 38px;gap:14px;align-items:start;padding:16px 0;border-bottom:1px solid var(--line)}
    .link-fields{display:grid;grid-template-columns:1fr 1.2fr 1.5fr 1.15fr 1.15fr;gap:10px}
    .field-help{display:block;color:var(--muted);font-size:10px;line-height:1.3;margin-top:5px}
    .operation-list{display:grid;gap:10px;margin-top:18px}
    .operation-row{display:grid;grid-template-columns:42px minmax(180px,1fr) auto 38px;align-items:center;gap:12px;padding:14px;border:1px solid var(--line);border-radius:7px;background:#faf9f5}
    .operation-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:6px;background:#e8ebff;color:#2251ff}
    .operation-main strong,.operation-main small{display:block}.operation-main small{color:var(--muted);margin-top:3px}.order-badge{font-weight:800;font-variant-numeric:tabular-nums}
    .operation-editor{grid-column:2/-1;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;padding-top:12px;border-top:1px solid var(--line)}
    .entity-list{display:grid;gap:8px;margin-top:18px}.entity-row{display:grid;grid-template-columns:44px minmax(0,1fr) auto auto auto;gap:12px;align-items:center;padding:13px 14px;border:1px solid var(--line);border-radius:7px;background:#faf9f5}.entity-copy strong,.entity-copy small{display:block}.entity-copy small{margin-top:3px;color:var(--muted);overflow-wrap:anywhere}.entity-actions{display:flex;gap:7px}.entity-row .btn{white-space:nowrap}
    .editor-modal{display:none;position:fixed;inset:0;z-index:300;align-items:center;justify-content:center;padding:20px}.editor-modal.open{display:flex}.editor-modal-backdrop{position:absolute;inset:0;background:rgba(12,12,10,.62);backdrop-filter:blur(8px)}.editor-modal-panel{position:relative;width:min(680px,100%);max-height:min(820px,calc(100dvh - 40px));overflow:auto;background:#fff;color:#171714;border:1px solid #cbc9c1;border-radius:10px;box-shadow:0 32px 100px rgba(0,0,0,.28)}.editor-modal-panel>header{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;padding:20px 22px;border-bottom:1px solid var(--line)}.editor-modal-panel h3{margin:0;font-size:21px}.editor-modal-panel header p{margin:4px 0 0;color:var(--muted);font-size:13px}.modal-close{width:36px;height:36px;border:1px solid var(--line);border-radius:6px;background:#f4f3ee;color:#171714}.modal-fields{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;padding:22px}.modal-fields .field.wide{grid-column:1/-1}.editor-modal-panel footer{display:flex;justify-content:flex-end;gap:9px;padding:16px 22px;border-top:1px solid var(--line);background:#faf9f5}.modal-checks{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}.modal-checks label{display:flex;align-items:center;gap:7px;padding:9px 11px;border:1px solid var(--line);border-radius:6px;background:#faf9f5}.modal-checks input{width:18px;height:18px}
    .icon-preview{width:44px;height:44px;display:grid;place-items:center;background:#e8ebff;color:#2251ff;border-radius:6px;font-size:18px}
    .icon-action{width:38px;height:38px;padding:0}.empty.compact{padding:18px;border-radius:0;border:0;border-bottom:1px solid var(--line)}
    @media(max-width:950px){.link-fields{grid-template-columns:1fr 1fr}}
    @media(max-width:760px){
      .editorial-admin #app{display:block;padding:0}.editorial-admin .sidebar{top:0;height:100dvh;border-radius:0!important;background:#191916!important}
      .editorial-admin .main{min-height:100dvh}.editorial-admin .topbar{padding:15px 14px}.editorial-admin .content{padding:20px 14px 90px}
      .editorial-admin .card{padding:16px}.link-editor{grid-template-columns:40px minmax(0,1fr) 36px;gap:9px}.link-fields{grid-template-columns:1fr}
      .operation-row{grid-template-columns:40px minmax(0,1fr) auto}.operation-row>.icon-action{grid-column:3}.operation-editor{grid-column:1/-1;grid-template-columns:1fr}
      .entity-row{grid-template-columns:40px minmax(0,1fr) auto}.entity-row>.pill,.entity-row>.order-badge{display:none}.entity-actions{grid-column:2/-1}.modal-fields{grid-template-columns:1fr}.modal-fields .field.wide{grid-column:auto}
    }
    /* Final shell constraints prevent legacy desktop rules from widening or offsetting pages. */
    .editorial-admin #app{grid-template-columns:250px minmax(0,1fr);width:100%;min-height:100vh;overflow:visible}
    .editorial-admin .backdrop{display:none;position:fixed;inset:0;grid-column:auto;grid-row:auto}
    .editorial-admin .sidebar{grid-column:1;grid-row:1;position:sticky;inset:auto;top:0;width:250px;min-width:250px;max-width:250px;align-self:start;overflow-x:hidden;overflow-y:auto}
    .editorial-admin .main{grid-column:2;grid-row:1;min-width:0;width:100%;max-width:100%;overflow:hidden}
    .editorial-admin .topbar{width:100%;min-width:0}
    .editorial-admin .topbar>div:first-child{min-width:0;overflow:hidden}
    .editorial-admin .content{width:100%;max-width:1180px;min-width:0;margin:0 auto}
    .editorial-admin .page,.editorial-admin .card,.editorial-admin .game-block,.editorial-admin .catalog-group,.editorial-admin .catalog-body,.editorial-admin .catalog-editor{min-width:0;max-width:100%}
    .editorial-admin .grid-2{grid-template-columns:repeat(2,minmax(0,1fr))}
    .editorial-admin .visibility-grid{grid-template-columns:repeat(3,minmax(0,1fr))}
    .editorial-admin .link-fields{grid-template-columns:repeat(2,minmax(0,1fr))}
    .editorial-admin input,.editorial-admin textarea,.editorial-admin select{min-width:0;max-width:100%}
    .editorial-admin .file-line,.editorial-admin .catalog-item-title{overflow-wrap:anywhere;word-break:break-word}
    @media(min-width:761px) and (max-width:1050px){
      .editorial-admin #app{grid-template-columns:220px minmax(0,1fr)}
      .editorial-admin .sidebar{width:220px;min-width:220px;max-width:220px;padding:18px 12px}
      .editorial-admin .content{padding:26px 22px 90px}
      .editorial-admin .visibility-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
    }
    @media(max-width:760px){
      .editorial-admin #app{display:block;overflow:hidden}
      .editorial-admin .sidebar{position:fixed;width:min(300px,88vw);min-width:0;max-width:none}
      .editorial-admin .backdrop.show{display:block}
      .editorial-admin .grid-2,.editorial-admin .visibility-grid,.editorial-admin .link-fields{grid-template-columns:minmax(0,1fr)}
    }
  .media-preview img,.media-preview video{object-fit:var(--media-fit,cover)!important;background:var(--surface)!important}
  .editorial-admin{font-family:Geist,system-ui,-apple-system,"Segoe UI",sans-serif}
  .editorial-admin .topbar{min-height:86px}.editorial-admin .topbar h2{font-size:clamp(24px,3vw,34px);font-weight:780}
  .editorial-admin .content{max-width:1360px}.editorial-admin .card{padding:clamp(18px,2.2vw,30px)}
  .editorial-admin .entity-list{gap:0;border:1px solid #d8d7d1;border-radius:10px;overflow:hidden;background:#fff}
  .editorial-admin .entity-row{border:0;border-bottom:1px solid #e2e1da;border-radius:0;background:#fff;padding:15px 17px;transition:background .18s,transform .18s}
  .editorial-admin .entity-row:last-child{border-bottom:0}.editorial-admin .entity-row:hover{background:#f4f3ee;transform:translateX(2px)}
  .editorial-admin .media-row{grid-template-columns:58px minmax(0,1fr) auto auto auto}
  .catalog-thumb{width:54px;height:54px;padding:0;border:1px solid #d5d3cc;border-radius:8px;overflow:hidden;background:#ecebe5;color:#77766f;display:grid;place-items:center}
  .catalog-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .7s cubic-bezier(.2,.8,.2,1),filter .3s}.catalog-thumb:hover img{transform:scale(1.09);filter:contrast(1.06)}
  .media-upload-field{padding:16px;border:1px dashed #bbb9b1;border-radius:8px;background:#f6f5f0}.media-upload-field small{display:block;margin-top:8px;color:#74736d}
  .image-viewer{display:none;position:fixed;inset:0;z-index:500;place-items:center;padding:24px}.image-viewer.open{display:grid}.image-viewer-backdrop{position:absolute;inset:0;background:rgba(8,8,7,.86);backdrop-filter:blur(18px)}
  .image-viewer-frame{position:relative;width:min(960px,100%);height:min(78vh,760px);display:grid;place-items:center}.image-viewer-frame img{max-width:100%;max-height:100%;object-fit:contain;border-radius:10px;box-shadow:0 40px 120px rgba(0,0,0,.5)}
  .image-viewer-close{position:absolute;right:0;top:-52px;width:40px;height:40px;border:1px solid rgba(255,255,255,.3);border-radius:50%;background:rgba(255,255,255,.1);color:#fff}
  @media(max-width:760px){.catalog-thumb{width:46px;height:46px}.editorial-admin .media-row{grid-template-columns:46px minmax(0,1fr) auto}.image-viewer{padding:14px}}
  /* Admin v2: horizontal operations studio, intentionally unlike the former sidebar dashboard. */
  .admin-v2{--v2-bg:#0d0e0c;--v2-panel:#171815;--v2-paper:#f2f0e8;--v2-line:#34352f;--v2-lime:#d9ff43;--v2-orange:#ff6a3d;background:var(--v2-bg);color:#f4f2ea;font-family:"Cabinet Grotesk",system-ui,sans-serif}
  .admin-v2:before{display:block;content:"";position:fixed;inset:0;pointer-events:none;background:radial-gradient(800px 420px at 82% -10%,rgba(217,255,67,.11),transparent 58%),linear-gradient(rgba(255,255,255,.018) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.018) 1px,transparent 1px);background-size:auto,44px 44px,44px 44px}
  .admin-v2 #app{display:block;width:100%;max-width:none;padding:0;background:transparent}
  .admin-v2 .sidebar{position:sticky;inset:auto;top:0;z-index:80;width:100%;min-width:0;max-width:none;height:82px;padding:12px 22px;display:flex;flex-direction:row;align-items:center;gap:5px;overflow-x:auto;overflow-y:hidden;border:0;border-bottom:1px solid var(--v2-line);border-radius:0;background:rgba(13,14,12,.9);backdrop-filter:blur(22px);color:#f4f2ea;box-shadow:none}
  .admin-v2 .side-brand{flex:0 0 auto;padding:0 22px 0 0;margin:0 14px 0 0;border:0;border-right:1px solid var(--v2-line)}
  .admin-v2 .logo{background:var(--v2-lime);color:#11120f;border-radius:50%;box-shadow:none}.admin-v2 .side-brand span{color:#8d9086}
  .admin-v2 .nav-btn{flex:0 0 auto;width:auto;padding:11px 13px;border:0;border-radius:3px;background:transparent;color:#92958b;font-size:13px;white-space:nowrap}
  .admin-v2 .nav-btn:hover{transform:none;background:#20211d;color:#fff}.admin-v2 .nav-btn.active{background:var(--v2-paper);color:#11120f}.admin-v2 .nav-btn.active:after{display:none}.admin-v2 .nav-ico{width:auto;height:auto;background:transparent;border:0}
  .admin-v2 .side-foot{flex:0 0 auto;margin:0 0 0 auto;padding:0 0 0 12px;border:0;border-left:1px solid var(--v2-line);display:flex;align-items:center}.admin-v2 .side-foot .meta{display:none}.admin-v2 .side-foot .btn{white-space:nowrap}
  .admin-v2 .main{display:block;width:100%;max-width:none;overflow:hidden;background:transparent}
  .admin-v2 .topbar{position:relative;top:auto;min-height:116px;padding:32px clamp(20px,5vw,76px);border:0;background:transparent;backdrop-filter:none}.admin-v2 .topbar h2{font-size:clamp(34px,5vw,64px);line-height:.95;letter-spacing:-.055em;color:#f4f2ea}.admin-v2 .topbar p{color:#888b82;margin-top:10px}
  .admin-v2 .mobile-toggle{display:none!important}.admin-v2 .top-actions .pill{background:transparent;color:#a6a99f;border-color:var(--v2-line)}
  .admin-v2 .btn{border-radius:3px}.admin-v2 .btn-accent,.admin-v2 .btn-primary{background:var(--v2-lime);color:#11120f}.admin-v2 .btn-danger{background:#2a1713;color:#ff9678;border-color:#603126}.admin-v2 .btn-ghost{color:#d9dbd2;border-color:#44463f;background:#1b1c19}
  .admin-v2 .content{width:100%;max-width:1540px;margin:0 auto;padding:14px clamp(20px,5vw,76px) 140px}
  .admin-v2 .card{background:var(--v2-panel);color:#efede5;border:1px solid var(--v2-line);border-radius:4px;padding:clamp(20px,3vw,36px);margin-bottom:18px;box-shadow:none}.admin-v2 .card:hover{transform:none;border-color:#52544c}.admin-v2 .card h3{font-size:23px}.admin-v2 .card .sub,.admin-v2 .field label,.admin-v2 .field .hint{color:#92958b}
  .admin-v2 .field input,.admin-v2 .field textarea,.admin-v2 .field select{background:#10110f;color:#f1efe7;border-color:#3d3f38;border-radius:3px}.admin-v2 .field input:focus,.admin-v2 .field textarea:focus,.admin-v2 .field select:focus{border-color:var(--v2-lime);box-shadow:0 0 0 2px rgba(217,255,67,.12)}
  .dashboard-hero{min-height:350px;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:end;gap:40px;padding:clamp(28px,5vw,70px);margin-bottom:18px;background:var(--v2-paper);color:#10110f;overflow:hidden;position:relative}.dashboard-hero:after{content:"";position:absolute;width:420px;height:420px;right:-120px;top:-200px;border:80px solid var(--v2-orange);border-radius:50%;opacity:.9}.dashboard-hero>div,.dashboard-sync{position:relative;z-index:1}.dashboard-hero p{font-size:12px;font-weight:800;letter-spacing:.2em}.dashboard-hero h1{max-width:980px;margin:16px 0 0;font-size:clamp(43px,7vw,104px);line-height:.84;letter-spacing:-.065em}.dashboard-sync{width:154px;height:154px;border:0;border-radius:50%;background:#11120f;color:#fff;display:grid;place-items:center;align-content:center;gap:10px;font-weight:700}.dashboard-sync i{font-size:24px}.dashboard-sync:hover{transform:rotate(-5deg) scale(1.03)}
  .metric-bento{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));grid-auto-flow:dense;gap:1px;background:var(--v2-line);border:1px solid var(--v2-line);margin-bottom:18px}.metric{grid-column:span 3;min-height:190px;padding:26px;display:flex;flex-direction:column;background:var(--v2-panel)}.metric-wide{grid-column:span 6;background:var(--v2-lime);color:#11120f}.metric span{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.1em}.metric b{margin-top:auto;font-size:clamp(42px,6vw,82px);line-height:.8;letter-spacing:-.065em}.metric small{margin-top:16px;color:inherit;opacity:.58}.dashboard-ledger{display:flex;align-items:center;justify-content:space-between;gap:20px}.system-state{display:flex;align-items:center;gap:11px;color:var(--v2-lime);font-weight:700}
  .admin-v2 .entity-list,.admin-v2 .entity-row{background:#151613;border-color:var(--v2-line)}.admin-v2 .entity-row:hover{background:#1e201b}.admin-v2 .entity-copy small{color:#8f9288}.admin-v2 .catalog-group{background:#141512;border-color:var(--v2-line)}.admin-v2 .catalog-summary:hover{background:#20211d}.admin-v2 .catalog-summary .summary-icon,.admin-v2 .visibility-icon,.admin-v2 .operation-icon{background:var(--v2-lime);color:#11120f}.admin-v2 .pill{border-radius:2px;background:#272822;color:#c4c7bb;border-color:#41433b}.admin-v2 .pill.ok{background:#173923;color:#8dffad}.admin-v2 .pill.warn{background:#3b2d13;color:#ffd272}
  .admin-v2 .editor-modal-panel{background:#181916;color:#f2f0e8;border-color:#484a42;border-radius:4px}.admin-v2 .editor-modal-panel footer,.admin-v2 .editor-modal-panel header{background:#181916;border-color:#34352f}.admin-v2 .modal-close{background:#242520;color:#fff;border-color:#44463f}
  @media(max-width:1050px){.admin-v2 .side-brand{display:none}.admin-v2 .side-foot{display:none}.metric,.metric-wide{grid-column:span 4}.dashboard-hero h1{font-size:clamp(42px,8vw,76px)}}
  @media(max-width:760px){.admin-v2 .sidebar{position:sticky!important;transform:none!important;width:100%!important;max-width:none!important;height:68px!important;padding:9px 12px!important;border-radius:0!important}.admin-v2 .nav-btn{padding:9px 11px}.admin-v2 .topbar{padding:24px 16px 18px;min-height:auto}.admin-v2 .topbar h2{font-size:39px}.admin-v2 .top-actions{width:100%;margin-top:16px}.admin-v2 .topbar{align-items:flex-start;flex-wrap:wrap}.admin-v2 .content{padding:12px 14px 100px}.dashboard-hero{min-height:380px;grid-template-columns:1fr;padding:28px 22px}.dashboard-hero h1{font-size:52px}.dashboard-sync{width:112px;height:112px}.metric,.metric-wide{grid-column:span 6;min-height:150px;padding:18px}.metric b{font-size:46px}.dashboard-ledger{align-items:flex-start;flex-direction:column}}
  /* Admin v3: fixed editorial sidebar and calm paper workspace. */
  .admin-v2{--v3-ink:#171724;--v3-side:#12121a;--v3-bg:#f4f4f8;--v3-paper:#fff;--v3-line:#dedee8;--v3-muted:#747486;--v3-accent:#6657e8;background:var(--v3-bg);color:var(--v3-ink);font-family:"Be Vietnam Pro",system-ui,-apple-system,"Segoe UI",sans-serif}
  .admin-v2:before{display:none}.admin-v2 #app{display:grid;grid-template-columns:272px minmax(0,1fr);min-height:100vh;background:var(--v3-bg)}
  .admin-v2 .sidebar{grid-column:1;grid-row:1;position:sticky;inset:auto;top:0;z-index:80;width:272px;min-width:272px;max-width:272px;height:100vh;padding:24px 18px;display:flex;flex-direction:column;align-items:stretch;gap:6px;overflow-x:hidden;overflow-y:auto;border:0;background:var(--v3-side);color:#f7f7fb;box-shadow:none}
  .admin-v2 .side-brand{display:flex;flex:0 0 auto;padding:0 6px 24px;margin:0 0 14px;border:0;border-bottom:1px solid #292934}.admin-v2 .side-brand .logo{width:42px;height:42px;border-radius:11px;background:#f7f7fb;color:#171724}.admin-v2 .side-brand span{color:#777786}
  .admin-v2 .nav-btn{flex:0 0 auto;width:100%;padding:12px 13px;border:0;border-radius:9px;background:transparent;color:#898999;font-size:14px;white-space:normal}.admin-v2 .nav-btn:hover{background:#1e1e29;color:#fff;transform:translateX(3px)}.admin-v2 .nav-btn.active{background:#f5f5fa;color:#171724}.admin-v2 .nav-ico{width:28px;height:28px;display:grid;place-items:center}
  .admin-v2 .side-foot{display:grid;flex:0 0 auto;margin:auto 0 0;padding:14px 0 0;border:0;border-top:1px solid #292934;gap:7px}.admin-v2 .side-foot .meta{display:block;color:#6f6f7d}.admin-v2 .settings-nav{margin-bottom:8px;color:#c9c9d5}.admin-v2 .settings-nav.active{background:#f5f5fa;color:#171724}.admin-v2 .side-foot .btn{width:100%}
  .admin-v2 .main{grid-column:2;grid-row:1;display:flex;min-width:0;width:100%;background:var(--v3-bg)}
  .admin-v2 .topbar{position:sticky;top:0;z-index:40;min-height:100px;padding:25px clamp(22px,4vw,58px);border-bottom:1px solid rgba(222,222,232,.9);background:rgba(244,244,248,.88);backdrop-filter:blur(20px)}.admin-v2 .topbar h2{font-size:clamp(30px,4vw,52px);line-height:1;letter-spacing:-.05em;color:var(--v3-ink)}.admin-v2 .topbar p{color:var(--v3-muted)}.admin-v2 .top-actions{margin:0;width:auto}.admin-v2 .top-actions .pill{display:inline-flex;background:#fff;color:#626274;border-color:var(--v3-line)}
  .admin-v2 .content{width:100%;max-width:1320px;margin:0 auto;padding:38px clamp(22px,4vw,58px) 110px}.admin-v2 .page{width:100%}
  .admin-v2 .card{background:var(--v3-paper);color:var(--v3-ink);border:1px solid var(--v3-line);border-radius:16px;padding:clamp(20px,3vw,32px);margin-bottom:18px;box-shadow:0 12px 35px rgba(32,31,54,.04)}.admin-v2 .card:hover{border-color:#c9c8d8;box-shadow:0 18px 46px rgba(32,31,54,.07)}.admin-v2 .card h3{font-size:22px;letter-spacing:-.025em}.admin-v2 .card .sub,.admin-v2 .field label,.admin-v2 .field .hint{color:var(--v3-muted)}
  .admin-v2 .field input,.admin-v2 .field textarea,.admin-v2 .field select{background:#f8f8fb;color:var(--v3-ink);border:1px solid var(--v3-line);border-radius:10px}.admin-v2 .field input:focus,.admin-v2 .field textarea:focus,.admin-v2 .field select:focus{border-color:var(--v3-accent);box-shadow:0 0 0 3px rgba(102,87,232,.1)}
  .admin-v2 .btn-accent,.admin-v2 .btn-primary{background:var(--v3-accent);color:#fff}.admin-v2 .btn-ghost{background:transparent;color:inherit;border-color:#3c3c49}.admin-v2 .btn-danger{background:#2a171d;color:#ffb2c2;border-color:#55303a}
  .dashboard-hero{min-height:310px;padding:clamp(30px,5vw,64px);border-radius:18px;background:#dedcff;color:#201e44}.dashboard-hero:after{width:340px;height:340px;right:-80px;top:-190px;border:66px solid #ff8e75;opacity:.85}.dashboard-hero h1{font-size:clamp(44px,6vw,86px);line-height:.88}.dashboard-sync{width:138px;height:138px;background:#201e44}.metric-bento{gap:12px;background:transparent;border:0}.metric{border-radius:16px;background:#fff;color:var(--v3-ink);border:1px solid var(--v3-line);min-height:178px}.metric-wide{background:#201e44;color:#fff;border-color:#201e44}.system-state{color:var(--v3-accent)}
  .admin-v2 .entity-list,.admin-v2 .entity-row{background:#fff;border-color:var(--v3-line)}.admin-v2 .entity-row:hover{background:#f8f8fb}.admin-v2 .entity-copy small{color:var(--v3-muted)}.admin-v2 .catalog-group{background:#fff;border-color:var(--v3-line);border-radius:13px}.admin-v2 .catalog-summary:hover{background:#f7f7fa}.admin-v2 .catalog-summary .summary-icon,.admin-v2 .visibility-icon,.admin-v2 .operation-icon{background:#eceafd;color:var(--v3-accent)}.admin-v2 .pill{background:#f0f0f5;color:#656576;border-color:#dcdce6;border-radius:6px}.admin-v2 .pill.ok{background:#e9f8ee;color:#267742}.admin-v2 .pill.warn{background:#fff3dc;color:#93621b}
  .admin-v2 .editor-modal-panel{background:#fff;color:var(--v3-ink);border-color:var(--v3-line);border-radius:16px}.admin-v2 .editor-modal-panel footer,.admin-v2 .editor-modal-panel header{background:#fff;border-color:var(--v3-line)}.admin-v2 .modal-close{background:#f4f4f8;color:var(--v3-ink);border-color:var(--v3-line)}
  .page-save-zone{margin-top:34px;padding-top:24px;border-top:1px solid var(--v3-line)}.page-save{width:100%;min-height:82px;padding:0 28px;border:0;border-radius:14px;background:var(--v3-ink);color:#fff;display:flex;align-items:center;justify-content:space-between;font-family:"Be Vietnam Pro",system-ui,sans-serif;font-size:clamp(20px,3vw,34px);font-weight:800;letter-spacing:-.03em;transition:transform .25s,background .25s}.page-save:hover{transform:translateY(-3px);background:var(--v3-accent)}.page-save:disabled{opacity:.55;cursor:wait;transform:none}.page-save i{width:46px;height:46px;border-radius:50%;display:grid;place-items:center;background:#fff;color:var(--v3-ink);font-size:16px}
  .admin-v2 .mobile-toggle{width:44px!important;height:44px!important;min-width:44px!important;padding:0!important;border-radius:8px!important;align-items:center;justify-content:center}
  .admin-v2 #toast{top:18px;right:18px;bottom:auto;padding:14px 16px 18px;overflow:hidden;border-radius:10px;background:#171724;color:#fff;border:1px solid #353548}
  .admin-v2 #toast:after{content:"";position:absolute;left:0;bottom:0;width:100%;height:4px;background:#8d82ff;transform-origin:left center}
  .admin-v2 #toast.timing:after{animation:toastCountdown 2.6s linear forwards}@keyframes toastCountdown{from{transform:scaleX(1)}to{transform:scaleX(0)}}
  @media(max-width:1050px){.admin-v2 #app{grid-template-columns:230px minmax(0,1fr)}.admin-v2 .sidebar{display:flex;width:230px;min-width:230px;max-width:230px}.metric,.metric-wide{grid-column:span 4}.dashboard-hero h1{font-size:62px}}
  @media(max-width:760px){.admin-v2 #app{display:block}.admin-v2 .sidebar{position:fixed!important;inset:0 auto 0 0!important;top:0!important;width:min(300px,88vw)!important;min-width:0!important;max-width:none!important;height:100dvh!important;padding:20px 16px!important;display:flex!important;transform:translate3d(-105%,0,0)!important;border-radius:0 20px 20px 0!important}.admin-v2 .sidebar.open{transform:translate3d(0,0,0)!important}.admin-v2 .backdrop.show{display:block}.admin-v2 .main{display:flex;width:100%}.admin-v2 .mobile-toggle{display:inline-flex!important;color:var(--v3-ink);border-color:var(--v3-line);background:#fff}.admin-v2 .topbar{padding:18px 14px;min-height:86px}.admin-v2 .topbar h2{font-size:34px}.admin-v2 .top-actions{display:flex;margin-left:auto}.admin-v2 .content{padding:22px 14px 90px}.dashboard-hero{min-height:360px;grid-template-columns:1fr}.dashboard-hero h1{font-size:50px}.metric,.metric-wide{grid-column:span 6;min-height:145px}.page-save{min-height:70px;padding:0 20px;font-size:22px}}
  .admin-v2 .media-upload-field{padding:0;border:0;background:transparent}
  .admin-v2 .media-preview{width:min(390px,100%);min-height:0;aspect-ratio:9/16;margin:22px auto 0;border-radius:18px;background:#e9e9f0}
  .admin-v2 .media-preview img,.admin-v2 .media-preview video{width:100%;height:100%}
  .admin-v2 .page.active,.admin-v2 #page-games.active,.admin-v2 #page-games.active *{filter:none}
  .admin-v2 .page.active,.admin-v2 #page-games.active .card,.admin-v2 #page-games.active .entity-row{opacity:1!important}
  .admin-v2 #page-games.active{animation:gamesPageIn .42s cubic-bezier(.22,1,.36,1) both}
  .admin-v2 #gamesList,.admin-v2 #gamesList.entity-list{gap:14px;background:transparent;border:0;overflow:visible}
  .admin-v2 #gamesList .entity-row,.admin-v2 #gamesList .entity-row *{opacity:1!important;filter:none!important}
  .admin-v2 #gamesList .entity-row{position:relative;isolation:isolate;overflow:hidden;min-height:82px;padding:16px 18px;border:1px solid #d9d9e5;border-radius:14px;background:#fff;color:#171724;box-shadow:0 8px 24px rgba(30,29,52,.045);transition:transform .32s cubic-bezier(.22,1,.36,1),border-color .25s,box-shadow .32s,background .25s}
  .admin-v2 #gamesList .entity-row:before{content:"";position:absolute;inset:10px auto 10px 0;width:3px;border-radius:0 4px 4px 0;background:var(--v3-accent);transform:scaleY(0);transform-origin:center;transition:transform .28s cubic-bezier(.22,1,.36,1)}
  .admin-v2 #gamesList .entity-row:hover{z-index:2;transform:translate3d(0,-4px,0) scale(1.006);border-color:#bcb8e8;background:#fff;box-shadow:0 18px 42px rgba(52,45,120,.13)}
  .admin-v2 #gamesList .entity-row:hover:before{transform:scaleY(1)}
  .admin-v2 #gamesList .entity-copy strong{color:#12121b;font-size:16px;font-weight:800;letter-spacing:-.015em}
  .admin-v2 #gamesList .entity-copy small{color:#626273;font-weight:500}
  .admin-v2 #gamesList .operation-icon{box-shadow:inset 0 0 0 1px rgba(102,87,232,.1);transition:transform .42s cubic-bezier(.22,1,.36,1),background .25s,color .25s}
  .admin-v2 #gamesList .entity-row:hover .operation-icon{transform:rotate(-7deg) scale(1.1);background:var(--v3-accent);color:#fff}
  .admin-v2 #gamesList .order-badge,.admin-v2 #gamesList .pill{color:#555568;font-weight:800}
  .admin-v2 #gamesList .btn{transition:transform .22s cubic-bezier(.22,1,.36,1),box-shadow .22s,background .22s,border-color .22s}
  .admin-v2 #gamesList .btn:hover{transform:translateY(-2px);box-shadow:0 7px 16px rgba(24,23,42,.12)}
  @keyframes gamesPageIn{from{transform:translate3d(0,12px,0)}to{transform:translate3d(0,0,0)}}
  @media(max-width:760px){.admin-v2 #gamesList,.admin-v2 #gamesList.entity-list{gap:11px}.admin-v2 #gamesList .entity-row{min-height:76px;padding:14px}.admin-v2 #gamesList .entity-row:hover{transform:translate3d(0,-2px,0)}}
  @media(prefers-reduced-motion:reduce){.admin-v2 #page-games.active{animation:none}.admin-v2 #gamesList .entity-row,.admin-v2 #gamesList .operation-icon,.admin-v2 #gamesList .btn{transition:none}}
  .admin-v2 .sidebar{transition:transform .45s cubic-bezier(.22,1,.36,1),box-shadow .35s}
  .admin-v2 .brand,.admin-v2 .nav-btn,.admin-v2 .card,.admin-v2 .metric,.admin-v2 .catalog-group,.admin-v2 .entity-row,.admin-v2 .field input,.admin-v2 .field textarea,.admin-v2 .field select,.admin-v2 .btn,.admin-v2 .switch,.admin-v2 .media-preview{will-change:transform;transition:transform .3s cubic-bezier(.22,1,.36,1),box-shadow .3s,border-color .25s,background-color .25s,color .25s}
  .admin-v2 .brand:hover{transform:none}
  .admin-v2 .brand .logo{transition:transform .5s cubic-bezier(.16,1,.3,1),box-shadow .3s}
  .admin-v2 .brand:hover .logo{transform:none;box-shadow:none}
  .admin-v2 .nav-btn{position:relative;overflow:hidden;transition:transform .22s cubic-bezier(.22,1,.36,1),background-color .22s,color .22s}
  .admin-v2 .nav-btn:after{display:none}
  .admin-v2 .nav-btn:hover{transform:translateX(2px)}
  .admin-v2 .nav-btn:active{transform:translateX(1px)}
  .admin-v2 .nav-btn:hover .nav-ico{transform:none;color:var(--v3-accent)}
  .admin-v2 .nav-btn.active:hover .nav-ico{color:inherit}
  .admin-v2 .nav-ico{transition:transform .38s cubic-bezier(.22,1,.36,1)}
  .admin-v2 .card:hover{transform:translateY(-4px);box-shadow:0 22px 54px rgba(32,31,54,.1)}
  .admin-v2 .metric:hover{transform:translateY(-5px) scale(1.012);border-color:#bbb7e8;box-shadow:0 20px 42px rgba(47,40,116,.12)}
  .admin-v2 .metric i,.admin-v2 .card h3 i{transition:transform .4s cubic-bezier(.22,1,.36,1)}
  .admin-v2 .metric:hover i,.admin-v2 .card:hover h3 i{transform:rotate(-8deg) scale(1.13)}
  .admin-v2 .catalog-group:hover{transform:translateY(-2px);box-shadow:0 16px 34px rgba(31,29,57,.08)}
  .admin-v2 .catalog-summary .chevron{transition:transform .38s cubic-bezier(.22,1,.36,1)}
  .admin-v2 .catalog-group[open]>.catalog-summary .chevron{transform:rotate(180deg)}
  .admin-v2 .field input:hover,.admin-v2 .field textarea:hover,.admin-v2 .field select:hover{border-color:#c4c0e9;background:#fff}
  .admin-v2 .field input:focus,.admin-v2 .field textarea:focus,.admin-v2 .field select:focus{transform:translateY(-1px)}
  .admin-v2 .btn:not(:disabled):hover{transform:translateY(-2px);box-shadow:0 9px 20px rgba(28,26,50,.13)}
  .admin-v2 .btn:not(:disabled):active{transform:translateY(0) scale(.97)}
  .admin-v2 .switch:hover{transform:scale(1.045)}
  .admin-v2 .media-preview:hover{transform:scale(1.012);box-shadow:0 24px 54px rgba(28,26,50,.14)}
  .admin-v2 .page-save:hover i{transform:rotate(-8deg) scale(1.08)}
  .admin-v2 .page-save i{transition:transform .4s cubic-bezier(.22,1,.36,1)}
  .admin-v2 .topbar h2{transition:letter-spacing .35s cubic-bezier(.22,1,.36,1)}
  .admin-v2 .topbar:hover h2{letter-spacing:-.035em}
  @media(hover:none){.admin-v2 .card:hover,.admin-v2 .metric:hover,.admin-v2 .catalog-group:hover,.admin-v2 .media-preview:hover{transform:none}}
  @media(prefers-reduced-motion:reduce){.admin-v2 *{scroll-behavior:auto!important;animation-duration:.01ms!important;animation-iteration-count:1!important;transition-duration:.01ms!important}}
  /* AI-tinted workspace: restrained aurora color, clearer navigation spacing and shared list motion. */
  .admin-v2{--v3-ink:#17162b;--v3-side:#111225;--v3-bg:#f2f3ff;--v3-paper:rgba(255,255,255,.88);--v3-line:#d9dcf0;--v3-muted:#696b82;--v3-accent:#6c5ce7;background:var(--v3-bg)}
  .admin-v2:before{display:block;content:"";position:fixed;inset:0 0 0 272px;z-index:0;pointer-events:none;background:radial-gradient(700px 480px at 88% 4%,rgba(74,222,255,.13),transparent 62%),radial-gradient(680px 500px at 18% 92%,rgba(127,96,255,.12),transparent 66%)}
  .admin-v2 .main{position:relative;z-index:1;background:transparent}
  .admin-v2 .sidebar{padding:24px 20px;background:linear-gradient(180deg,#111225 0%,#17132d 100%);box-shadow:12px 0 40px rgba(38,31,90,.08)}
  .admin-v2 .sidebar>.nav-btn{width:calc(100% - 12px);margin-inline:6px;padding:12px 15px;border:1px solid transparent}
  .admin-v2 .sidebar>.nav-btn:hover{border-color:rgba(148,136,255,.18);background:rgba(255,255,255,.06)}
  .admin-v2 .sidebar>.nav-btn.active{border-color:rgba(255,255,255,.84);background:linear-gradient(135deg,#fff,#eeeefe);box-shadow:0 10px 28px rgba(0,0,0,.16)}
  .admin-v2 .side-foot{margin-inline:6px}
  .admin-v2 .topbar{background:rgba(242,243,255,.78);border-color:rgba(207,211,235,.8)}
  .admin-v2 .card,.admin-v2 .metric,.admin-v2 .catalog-group,.admin-v2 .entity-row{backdrop-filter:blur(16px);box-shadow:0 12px 34px rgba(42,38,91,.055)}
  .admin-v2 .entity-list{gap:12px;background:transparent;border:0}
  .admin-v2 .entity-row{position:relative;overflow:hidden;border-radius:13px;background:rgba(255,255,255,.9);border:1px solid var(--v3-line)}
  .admin-v2 .entity-row:before{content:"";position:absolute;inset:9px auto 9px 0;width:3px;border-radius:0 4px 4px 0;background:linear-gradient(180deg,#7c6cff,#35ccec);transform:scaleY(0);transform-origin:center;transition:transform .28s cubic-bezier(.22,1,.36,1)}
  .admin-v2 .entity-row:hover{transform:translate3d(0,-4px,0) scale(1.004);border-color:#bbb8ed;background:#fff;box-shadow:0 18px 42px rgba(58,48,133,.12)}
  .admin-v2 .entity-row:hover:before{transform:scaleY(1)}
  .admin-v2 .entity-row:hover .operation-icon,.admin-v2 .catalog-summary:hover .summary-icon{background:linear-gradient(135deg,#7564eb,#35ccec);color:#fff}
  .admin-v2 .catalog-group{overflow:hidden;background:rgba(255,255,255,.9)}
  .admin-v2 .catalog-summary:hover{background:linear-gradient(90deg,rgba(108,92,231,.07),rgba(53,204,236,.045))}
  .admin-v2 .btn-accent,.admin-v2 .btn-primary{background:linear-gradient(135deg,#6c5ce7,#4b8df8);box-shadow:0 8px 20px rgba(91,79,214,.2)}
  .admin-v2 .page-save{background:linear-gradient(110deg,#17162b 0%,#40338f 62%,#236f91 100%)}
  .admin-v2 .page-save:hover{background:linear-gradient(110deg,#251f50 0%,#5d4ee0 58%,#2c91b8 100%)}
  @media(max-width:1050px){.admin-v2:before{inset:0 0 0 230px}}
  @media(max-width:760px){.admin-v2:before{inset:0}.admin-v2 .sidebar{padding:20px 14px!important}.admin-v2 .sidebar>.nav-btn{width:calc(100% - 10px);margin-inline:5px}.admin-v2 .entity-list{gap:10px}.admin-v2 .entity-row:hover{transform:translateY(-2px)}}
  /* Keep every sidebar action on the exact same vertical axis as Dashboard. */
  .admin-v2 .sidebar>.nav-btn{
    display:grid;
    grid-template-columns:28px minmax(0,1fr);
    align-items:center;
    column-gap:10px;
    text-align:left;
    box-sizing:border-box;
    transform:none!important;
  }
  .admin-v2 .sidebar>.nav-btn:hover,
  .admin-v2 .sidebar>.nav-btn:active{transform:none!important}

  /* Midnight Teal + Coral — final theme layer. */
  .admin-v2{
    color-scheme:dark;
    --v3-ink:#f6f2e8;--v3-side:#071918;--v3-bg:#092321;--v3-paper:rgba(13,48,45,.88);
    --v3-line:#24534e;--v3-muted:#91aaa5;--v3-accent:#ff7657;
    background:#092321;color:#f6f2e8;font-family:"Outfit","Be Vietnam Pro",system-ui,sans-serif;
  }
  .admin-v2:before{display:block;inset:0 0 0 272px;background:radial-gradient(760px 540px at 92% 2%,rgba(255,118,87,.16),transparent 62%),radial-gradient(760px 620px at 12% 96%,rgba(39,200,180,.14),transparent 65%),linear-gradient(135deg,#092321,#0b2927 54%,#102b29)}
  .admin-v2 #app{background:transparent}.admin-v2 .main{background:transparent;color:#f6f2e8}
  .admin-v2 .sidebar{background:linear-gradient(180deg,#061615 0%,#09201e 58%,#0d2926 100%);box-shadow:18px 0 60px rgba(0,0,0,.25);border-right:1px solid #173d39}
  .admin-v2 .side-brand{border-color:#21413e}.admin-v2 .side-brand .logo{background:#ff7657;color:#1a1713;box-shadow:0 10px 28px rgba(255,118,87,.26)}.admin-v2 .side-brand span,.admin-v2 .side-foot .meta{color:#6f918b}
  .admin-v2 .sidebar>.nav-btn{color:#8eaaa5;border-color:transparent}.admin-v2 .sidebar>.nav-btn:hover{background:#11312e;color:#fff;border-color:#24534e}.admin-v2 .sidebar>.nav-btn:hover .nav-ico{color:#ffb24a}
  .admin-v2 .sidebar>.nav-btn.active{background:#f4eedf;color:#102320;border-color:#f4eedf;box-shadow:0 12px 30px rgba(0,0,0,.28)}
  .admin-v2 .side-foot{border-color:#21413e}
  .admin-v2 .topbar{background:rgba(7,27,25,.78);border-color:#24534e;box-shadow:0 12px 30px rgba(0,0,0,.1)}.admin-v2 .topbar h2{color:#fff8ea}.admin-v2 .topbar p{color:#91aaa5}.admin-v2 .top-actions .pill{background:#103531;color:#acd1c9;border-color:#28605a}
  .admin-v2 .content{max-width:1380px}
  .admin-v2 .card,.admin-v2 .metric,.admin-v2 .catalog-group,.admin-v2 .entity-row{background:linear-gradient(145deg,rgba(17,56,52,.94),rgba(11,43,40,.92));color:#f6f2e8;border-color:#285751;box-shadow:0 18px 48px rgba(0,0,0,.14);backdrop-filter:blur(18px)}
  .admin-v2 .card:hover,.admin-v2 .catalog-group:hover,.admin-v2 .entity-row:hover{background:linear-gradient(145deg,#153e3a,#0d312e);border-color:#3f766f;box-shadow:0 22px 54px rgba(0,0,0,.22)}
  .admin-v2 .card .sub,.admin-v2 .field label,.admin-v2 .field .hint,.admin-v2 .entity-copy small{color:#91aaa5}
  .admin-v2 .field input,.admin-v2 .field textarea,.admin-v2 .field select{background:#071f1d;color:#f8f4e9;border-color:#2b5752}.admin-v2 .field input:hover,.admin-v2 .field textarea:hover,.admin-v2 .field select:hover{background:#0b2926;border-color:#4d7e77}.admin-v2 .field input:focus,.admin-v2 .field textarea:focus,.admin-v2 .field select:focus{border-color:#ff7657;box-shadow:0 0 0 3px rgba(255,118,87,.14)}
  .admin-v2 .btn-accent,.admin-v2 .btn-primary{background:#ff7657;color:#1b1915;box-shadow:0 9px 22px rgba(255,118,87,.22)}.admin-v2 .btn-ghost{background:#12332f;color:#f3eee4;border-color:#35635d}.admin-v2 .btn-danger{background:#3b1d20;color:#ffb4ae;border-color:#714047}
  .dashboard-hero{grid-template-columns:minmax(0,1fr) auto;background:linear-gradient(125deg,#f3ead8 0%,#dce8d8 70%,#c9e4db 100%);color:#102320;border:1px solid rgba(255,255,255,.45);box-shadow:0 24px 70px rgba(0,0,0,.18)}.dashboard-hero:after{border-color:#ff7657}.dashboard-sync{background:#092321;color:#fff5e7}.dashboard-sync:hover{background:#ff7657;color:#1b1915}
  .metric-bento{grid-auto-flow:dense}.admin-v2 .metric-wide{background:#ff7657;color:#1b1915;border-color:#ff7657}.admin-v2 .metric:nth-child(2),.admin-v2 .metric:nth-child(3){background:#f0e6d4;color:#172521;border-color:#f0e6d4}.admin-v2 .metric:hover{border-color:#ffb24a;box-shadow:0 20px 44px rgba(0,0,0,.22)}
  .admin-v2 .catalog-summary:hover{background:rgba(255,118,87,.08)}.admin-v2 .catalog-summary .summary-icon,.admin-v2 .visibility-icon,.admin-v2 .operation-icon{background:#173e39;color:#ffb24a}.admin-v2 .entity-row:hover .operation-icon,.admin-v2 .catalog-summary:hover .summary-icon{background:#ff7657;color:#17211e}
  .admin-v2 .pill{background:#153b37;color:#a9c8c2;border-color:#32645e}.admin-v2 .pill.ok{background:#153e2d;color:#8be5aa}.admin-v2 .pill.warn{background:#49351c;color:#ffd083}
  .admin-v2 .visibility-option{background:#0b2926;color:#f6f2e8;border-color:#285751}.admin-v2 .visibility-option:hover{background:#123732;border-color:#ff7657}
  .admin-v2 .page-save{background:linear-gradient(110deg,#f3ead8 0%,#ffb24a 55%,#ff7657 100%);color:#18231f;box-shadow:0 20px 48px rgba(0,0,0,.2)}.admin-v2 .page-save:hover{background:linear-gradient(110deg,#fff5e6,#ffc05e 50%,#ff896d)}.admin-v2 .page-save i{background:#092321;color:#fff}
  .admin-v2 #toast{background:#071918;color:#fff6e8;border-color:#35635d}.admin-v2 #toast:after{background:#ff7657}
  .admin-v2 .editor-modal-panel{background:#0d302d;color:#f6f2e8;border-color:#35635d}.admin-v2 .editor-modal-panel header,.admin-v2 .editor-modal-panel footer{background:#0b2926;border-color:#285751}.admin-v2 .modal-close{background:#153b37;color:#fff;border-color:#35635d}
  .admin-v2 .media-preview{background:#071f1d;border-color:#35635d}
  @media(max-width:1050px){.admin-v2:before{inset:0 0 0 230px}}
  @media(max-width:760px){.admin-v2:before{inset:0}.admin-v2 .mobile-toggle{background:#f3ead8;color:#102320;border-color:#f3ead8}}

  /* Gallery Ivory + Cobalt — complete workspace and login redesign. */
  .admin-v2{color-scheme:light;--v3-ink:#171717;--v3-side:#151515;--v3-bg:#f1efe9;--v3-paper:#fff;--v3-line:#d9d5ca;--v3-muted:#77736a;--v3-accent:#1649ff;background:#f1efe9;color:#171717;font-family:Geist,"Be Vietnam Pro",system-ui,sans-serif}
  .admin-v2:before{display:block;inset:0 0 0 272px;background:radial-gradient(760px 520px at 96% 0%,rgba(22,73,255,.09),transparent 65%),radial-gradient(620px 520px at 15% 100%,rgba(255,196,61,.09),transparent 68%),#f1efe9}
  .admin-v2 #app,.admin-v2 .main{background:transparent;color:#171717}
  .admin-v2 .sidebar{background:#151515;color:#f8f6f0;border-right:0;box-shadow:12px 0 42px rgba(20,20,20,.09)}
  .admin-v2 .side-brand{border-color:#30302e}.admin-v2 .side-brand .logo{background:#1649ff;color:#fff;box-shadow:none}.admin-v2 .side-brand span,.admin-v2 .side-foot .meta{color:#777773}.admin-v2 .side-foot{border-color:#30302e}
  .admin-v2 .sidebar>.nav-btn{color:#92928e;border-color:transparent}.admin-v2 .sidebar>.nav-btn:hover{background:#242422;color:#fff;border-color:#333331}.admin-v2 .sidebar>.nav-btn:hover .nav-ico{color:#87a2ff}.admin-v2 .sidebar>.nav-btn.active{background:#f5f2e9;color:#171717;border-color:#f5f2e9;box-shadow:none}
  .admin-v2 .topbar{background:rgba(241,239,233,.86);border-color:#d9d5ca;box-shadow:none}.admin-v2 .topbar h2{color:#171717}.admin-v2 .topbar p{color:#77736a}.admin-v2 .top-actions .pill{background:#fff;color:#62605a;border-color:#d9d5ca}
  .admin-v2 .card,.admin-v2 .metric,.admin-v2 .catalog-group,.admin-v2 .entity-row{background:rgba(255,255,255,.92);color:#171717;border-color:#d9d5ca;box-shadow:0 12px 34px rgba(28,27,23,.045);backdrop-filter:blur(16px)}
  .admin-v2 .card:hover,.admin-v2 .catalog-group:hover,.admin-v2 .entity-row:hover{background:#fff;border-color:#bdb8aa;box-shadow:0 20px 44px rgba(28,27,23,.08)}
  .admin-v2 .card .sub,.admin-v2 .field label,.admin-v2 .field .hint,.admin-v2 .entity-copy small{color:#77736a}
  .admin-v2 .field input,.admin-v2 .field textarea,.admin-v2 .field select{background:#f8f6f0;color:#171717;border-color:#d9d5ca}.admin-v2 .field input:hover,.admin-v2 .field textarea:hover,.admin-v2 .field select:hover{background:#fff;border-color:#aaa59a}.admin-v2 .field input:focus,.admin-v2 .field textarea:focus,.admin-v2 .field select:focus{border-color:#1649ff;box-shadow:0 0 0 3px rgba(22,73,255,.1)}
  .admin-v2 .btn-accent,.admin-v2 .btn-primary{background:#1649ff;color:#fff;box-shadow:none}.admin-v2 .btn-ghost{background:transparent;color:#242424;border-color:#aaa59a}.admin-v2 .sidebar .btn-ghost{color:#f2f0ea;border-color:#4b4b47}.admin-v2 .btn-danger{background:#fff0ed;color:#a82b23;border-color:#e3b2ad}
  .dashboard-hero{background:#e1e7ff;color:#101d54;border:1px solid #c9d3ff;box-shadow:none}.dashboard-hero:after{border-color:#ffcc4d}.dashboard-sync{background:#1649ff;color:#fff}.dashboard-sync:hover{background:#0d35c8;color:#fff}
  .admin-v2 .metric-wide{background:#171717;color:#fff;border-color:#171717}.admin-v2 .metric:nth-child(2),.admin-v2 .metric:nth-child(3){background:#1649ff;color:#fff;border-color:#1649ff}.admin-v2 .metric:hover{border-color:#1649ff;box-shadow:0 18px 38px rgba(22,73,255,.1)}
  .admin-v2 .catalog-summary:hover{background:#f3f5ff}.admin-v2 .catalog-summary .summary-icon,.admin-v2 .visibility-icon,.admin-v2 .operation-icon{background:#e8edff;color:#1649ff}.admin-v2 .entity-row:hover .operation-icon,.admin-v2 .catalog-summary:hover .summary-icon{background:#1649ff;color:#fff}
  .admin-v2 .pill{background:#f0eee8;color:#656159;border-color:#d9d5ca}.admin-v2 .pill.ok{background:#e9f7ed;color:#267743}.admin-v2 .pill.warn{background:#fff3d9;color:#8c5c13}
  .admin-v2 .visibility-option{background:#faf9f5;color:#171717;border-color:#d9d5ca}.admin-v2 .visibility-option:hover{background:#f2f5ff;border-color:#1649ff}
  .admin-v2 .page-save{background:#171717;color:#fff;box-shadow:0 16px 34px rgba(20,20,20,.12)}.admin-v2 .page-save:hover{background:#1649ff}.admin-v2 .page-save i{background:#fff;color:#171717}
  .admin-v2 #toast{background:#171717;color:#fff;border-color:#353535}.admin-v2 #toast:after{background:#6f91ff}
  .admin-v2 .editor-modal-panel{background:#fff;color:#171717;border-color:#d9d5ca}.admin-v2 .editor-modal-panel header,.admin-v2 .editor-modal-panel footer{background:#fff;border-color:#d9d5ca}.admin-v2 .editor-modal-panel footer{background:#f8f6f0}.admin-v2 .modal-close{background:#f1efe9;color:#171717;border-color:#d9d5ca}.admin-v2 .media-preview{background:#ece9e1;border-color:#c8c3b7}

  .admin-v2 #loginScreen{position:relative;min-height:100dvh;display:grid;grid-template-columns:minmax(0,1.2fr) minmax(380px,.8fr);gap:0;padding:22px;background:#edeae2;overflow:hidden}
  .admin-v2 #loginScreen:before{content:"";position:absolute;inset:0;pointer-events:none;background:radial-gradient(620px 420px at 100% 0%,rgba(22,73,255,.11),transparent 68%)}
  .login-editorial{position:relative;min-height:calc(100dvh - 44px);overflow:hidden;border-radius:22px;background:linear-gradient(180deg,rgba(15,15,15,.12),rgba(15,15,15,.82)),url('https://picsum.photos/seed/brutalist-architecture/1600/1800') center/cover;color:#fff;filter:contrast(1.04)}
  .login-editorial:after{content:"";position:absolute;inset:0;background:linear-gradient(120deg,rgba(22,73,255,.36),transparent 52%);mix-blend-mode:screen}
  .login-editorial-copy{position:absolute;z-index:1;left:clamp(30px,5vw,76px);right:clamp(30px,5vw,76px);bottom:clamp(34px,7vw,92px);max-width:760px}.login-editorial-copy span{display:block;margin-bottom:20px;font-size:12px;font-weight:800;letter-spacing:.2em}.login-editorial-copy h2{max-width:720px;margin:0;font-size:clamp(3rem,6vw,6.8rem);line-height:.88;letter-spacing:-.065em}.login-editorial-copy p{max-width:620px;margin:28px 0 0;color:rgba(255,255,255,.76);font-size:clamp(15px,1.4vw,20px);line-height:1.55}
  .admin-v2 #loginScreen>.login-card{position:relative;align-self:center;justify-self:center;width:min(430px,calc(100% - 48px));margin:0;padding:42px;background:#fff;color:#171717;border:1px solid #d9d5ca;border-radius:18px;box-shadow:0 30px 80px rgba(38,36,29,.12);backdrop-filter:none;overflow:visible}.admin-v2 #loginScreen>.login-card:before{display:none}
  .admin-v2 #loginScreen .brand{margin-bottom:30px}.admin-v2 #loginScreen .brand .logo{width:52px;height:52px;border-radius:14px;background:#1649ff;color:#fff;box-shadow:none}.admin-v2 #loginScreen .brand h1{color:#171717;font-size:25px;letter-spacing:-.035em}.admin-v2 #loginScreen .brand p{color:#77736a}
  .admin-v2 #loginScreen .field label{color:#625f58}.admin-v2 #loginScreen .field input{min-height:54px;background:#f8f6f0;color:#171717;border-color:#d9d5ca}.admin-v2 #loginScreen .btn-primary{min-height:54px;margin-top:6px;border-radius:10px;background:#1649ff;color:#fff}.admin-v2 #loginScreen .btn-primary:hover{background:#0e38cd;box-shadow:0 12px 28px rgba(22,73,255,.2)}.admin-v2 #loginScreen .login-hint{color:#8a867d}
  @media(max-width:1050px){.admin-v2:before{inset:0 0 0 230px}.admin-v2 #loginScreen{grid-template-columns:1fr 430px}.login-editorial-copy h2{font-size:clamp(3rem,6.3vw,5.2rem)}}
  @media(max-width:760px){.admin-v2:before{inset:0}.admin-v2 #loginScreen{display:grid;grid-template-columns:1fr;padding:14px;background:#edeae2}.login-editorial{position:absolute;inset:14px;min-height:0;border-radius:20px}.login-editorial:before{content:"";position:absolute;inset:0;background:rgba(12,12,12,.48)}.login-editorial-copy{display:none}.admin-v2 #loginScreen>.login-card{width:min(430px,calc(100% - 28px));padding:28px 22px;background:rgba(255,255,255,.94);backdrop-filter:blur(18px)}.admin-v2 .mobile-toggle{background:#fff;color:#171717;border-color:#d9d5ca}}
</style>
</head>
<body class="editorial-admin admin-v2">

<section id="loginScreen">
  <aside class="login-editorial" aria-hidden="true">
    <div class="login-editorial-copy">
      <span>APEX CONTROL</span>
      <h2>Điều hành mọi nội dung từ một nơi.</h2>
      <p>Quản lý ứng dụng, danh mục, phiên bản và trải nghiệm người dùng trong một workspace thống nhất.</p>
    </div>
  </aside>
  <div class="login-card">
    <div class="brand">
      <div class="logo"><i class="fa-solid fa-bolt"></i></div>
      <div><h1>APEX Admin</h1><p>Thông báo · Giao diện · Game · Danh mục</p></div>
    </div>
    <form id="loginForm" autocomplete="on">
      <div class="field">
        <label for="loginPassword">Mật khẩu quản trị</label>
        <input id="loginPassword" name="password" type="password" placeholder="Mật khẩu trong api.php" required autofocus>
      </div>
      <button class="btn btn-primary" id="loginBtn" type="submit"><i class="fa-solid fa-shield-halved"></i> Đăng nhập</button>
      <div class="login-error" id="loginError">Sai mật khẩu hoặc không kết nối được server.</div>
      <p class="login-hint">Phiên lưu trên trình duyệt đến khi đăng xuất.</p>
    </form>
  </div>
</section>

<div id="app" class="hidden">
  <div class="backdrop" id="backdrop"></div>
  <aside class="sidebar" id="sidebar">
    <div class="side-brand">
      <div class="logo"><i class="fa-solid fa-bolt"></i></div>
      <div><strong>APEX Admin</strong><span>Control panel</span></div>
    </div>
    <button class="nav-btn active" data-page="dashboard"><span class="nav-ico"><i class="fa-solid fa-chart-pie"></i></span> Dashboard</button>
    <button class="nav-btn" data-page="games"><span class="nav-ico"><i class="fa-solid fa-gamepad"></i></span> Ứng dụng</button>
    <button class="nav-btn" data-page="catalog"><span class="nav-ico"><i class="fa-solid fa-box-archive"></i></span> Danh mục</button>
    <button class="nav-btn" data-page="tabs"><span class="nav-ico"><i class="fa-solid fa-table-columns"></i></span> Thanh tab</button>
    <button class="nav-btn" data-page="info"><span class="nav-ico"><i class="fa-solid fa-globe"></i></span> Landing Page</button>
    <button class="nav-btn" data-page="version"><span class="nav-ico"><i class="fa-solid fa-code-branch"></i></span> VERSION</button>
    <button class="nav-btn" data-page="appearance"><span class="nav-ico"><i class="fa-solid fa-wand-magic-sparkles"></i></span> Giao diện</button>
    <button class="nav-btn" data-page="settings"><span class="nav-ico"><i class="fa-solid fa-sliders"></i></span> Cài đặt</button>
    <div class="side-foot">
      <div class="meta" id="sessionMeta">Đã đăng nhập</div>
      <button class="btn btn-ghost btn-sm" id="reloadBtn" type="button"><i class="fa-solid fa-rotate"></i> Tải lại dữ liệu</button>
      <button class="btn btn-danger btn-sm" id="logoutBtn" type="button"><i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất</button>
    </div>
  </aside>

  <div class="main">
    <header class="topbar">
      <div style="display:flex;align-items:flex-start;gap:10px">
        <button class="btn btn-ghost btn-sm mobile-toggle" id="menuBtn" type="button"><i class="fa-solid fa-bars"></i></button>
        <div>
          <h2 id="pageTitle">Dashboard</h2>
          <p id="pageSub">Toàn cảnh dữ liệu và lưu lượng ứng dụng</p>
        </div>
      </div>
    </header>

    <div class="content">
      <section class="page active" id="page-dashboard">
        <div class="dashboard-hero">
          <div><p>APEX CONTROL</p><h1>Dữ liệu ứng dụng,<br>trong một nhịp nhìn.</h1></div>
          <button class="dashboard-sync" type="button" id="dashboardReload"><i class="fa-solid fa-arrow-rotate-right"></i><span>Làm mới số liệu</span></button>
        </div>
        <div class="metric-bento">
          <article class="metric metric-wide"><span>Tổng request dữ liệu</span><b id="statRequestsTotal">0</b><small>Toàn bộ thời gian</small></article>
          <article class="metric"><span>Hôm nay</span><b id="statRequestsToday">0</b><small>Request từ app</small></article>
          <article class="metric"><span>7 ngày</span><b id="statRequestsWeek">0</b><small>Lưu lượng gần nhất</small></article>
          <article class="metric"><span>Ứng dụng</span><b id="statGames">0</b><small>Đang quản lý</small></article>
          <article class="metric"><span>Danh mục</span><b id="statItems">0</b><small>Tổng mục đang quản lý</small></article>
          <article class="metric"><span>Thanh tab</span><b id="statTabs">0</b><small>Tất cả ứng dụng</small></article>
        </div>
        <div class="dashboard-ledger card"><div><h3>Trạng thái hệ thống</h3><p class="sub">Dữ liệu cấu hình và SQL được kiểm tra khi tải Dashboard.</p></div><div class="system-state"><i class="fa-solid fa-database"></i><span id="dashboardState">Đang đồng bộ</span></div></div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE DASHBOARD</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-settings">
        <div class="card">
          <h3>Thông báo khi vào app</h3>
          <p class="sub">Hiện mỗi lần mở app (trừ khi user ẩn 1 giờ). Lưu web → app thấy ngay.</p>
          <div class="field"><label>Tiêu đề</label><input id="noticeTitle" placeholder="Thông báo"></div>
          <div class="field"><label>Nội dung</label><textarea id="noticeMessage" placeholder="Nội dung..."></textarea></div>
        </div>
        <div class="card"><h3>Chế độ bảo trì</h3><p class="sub">Khi bật, người dùng chỉ xem được thông báo này và không thể dùng ứng dụng.</p><label class="check"><input type="checkbox" id="maintenanceEnabled"> Bật chế độ bảo trì</label><div class="field"><label>Tiêu đề</label><input id="maintenanceTitle" placeholder="MAINTENANCE"></div><div class="field"><label>Nội dung bảo trì</label><textarea id="maintenanceMessage" placeholder="Hệ thống đang bảo trì..."></textarea></div></div>
        <div class="card">
          <h3>Nhạc nền</h3>
          <p class="sub">App tự phát khi vào nếu được bật. Chọn một nguồn nhạc duy nhất bên dưới.</p>
          <label class="check"><input type="checkbox" id="musicEnabled"> Bật nhạc nền trên app</label>
          <div class="field"><label>Nguồn nhạc</label><select id="musicSource"><option value="audio">Link nhạc tự đặt</option><option value="video">Nhạc từ video</option></select></div>
          <div id="musicAudioFields">
            <div class="field"><label>Link nhạc</label><input id="musicURL" placeholder="https://.../song.mp3"><div class="hint">Hỗ trợ MP3, M4A, AAC và các định dạng audio phổ biến.</div></div>
            <div class="field"><label>Upload nhạc</label><input type="file" id="musicUpload" accept="audio/*,.mp3,.m4a,.aac,.wav,.ogg"></div>
          </div>
          <div class="field" id="musicVideoFields"><div class="hint">App sẽ phát track âm thanh của video nền đã upload trong phần Giao diện.</div></div>
        </div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE SETTINGS</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-appearance">
        <div class="card"><h3>Độ mờ card toàn ứng dụng</h3><p class="sub">Giảm để nhìn rõ ảnh/video nền hơn; tăng để nội dung dễ đọc hơn.</p><div class="field"><label>Độ mờ: <strong id="cardOpacityValue">92%</strong></label><input id="cardOpacity" type="range" min="15" max="100" step="1" value="92"></div></div>
        <div class="card">
          <h3>Trang hiển thị trong ứng dụng</h3>
          <p class="sub">Tắt một trang để ẩn tab đó khỏi thanh điều hướng. Thay đổi có hiệu lực sau khi app tải lại cấu hình.</p>
          <div class="visibility-grid">
            <label class="visibility-option"><span class="visibility-icon"><i class="fa-solid fa-house"></i></span><span><strong>Trang chủ</strong><small>Thiết bị, mạng xã hội và ghi công</small></span><input type="checkbox" id="pageHomeVisible"></label>
            <label class="visibility-option"><span class="visibility-icon"><i class="fa-solid fa-gamepad"></i></span><span><strong>GAME</strong><small>Danh sách game và cấu hình</small></span><input type="checkbox" id="pageGamesVisible"></label>
            <label class="visibility-option"><span class="visibility-icon"><i class="fa-solid fa-layer-group"></i></span><span><strong>PATCH</strong><small>Tạo, nhập và quản lý patch</small></span><input type="checkbox" id="pagePatchesVisible"></label>
            <label class="visibility-option"><span class="visibility-icon"><i class="fa-solid fa-image"></i></span><span><strong>Hình nền</strong><small>Công cụ quản lý hình nền thiết bị</small></span><input type="checkbox" id="pageWallpaperVisible"></label>
            <label class="visibility-option"><span class="visibility-icon"><i class="fa-solid fa-folder"></i></span><span><strong>Tệp</strong><small>Trình quản lý dữ liệu ứng dụng</small></span><input type="checkbox" id="pageFilesVisible"></label>
            <label class="visibility-option"><span class="visibility-icon"><i class="fa-solid fa-broom"></i></span><span><strong>Dọn dẹp</strong><small>Công cụ giải phóng bộ nhớ đệm</small></span><input type="checkbox" id="pageCleanerVisible"></label>
          </div>
        </div>
        <div class="card">
          <h3>Ảnh / video nền</h3>
          <p class="sub">Nền toàn app. Ảnh hỗ trợ JPG, PNG, WEBP, GIF, HEIC/HEIF, BMP, TIFF, AVIF và JPEG XL. Video nên dùng MP4/MOV mã hóa H.264 hoặc HEVC để tương thích tốt nhất với iOS.</p>
          <div class="field">
            <label>Kiểu nền</label>
            <select id="backgroundType">
              <option value="none">Mặc định (theme app)</option>
              <option value="image">Ảnh</option>
              <option value="video">Video</option>
            </select>
          </div>
          <div class="field">
            <label>Chế độ hiển thị ảnh / video</label>
            <select id="backgroundContentMode">
              <option value="cover">Phủ kín — zoom và cắt phần thừa</option>
              <option value="fit">Toàn bộ — không zoom, không cắt</option>
            </select>
          </div>
          <div class="grid-2">
            <div class="field">
              <label>Link ảnh nền</label>
              <input id="backgroundImageURL" placeholder="https://.../bg.jpg">
              <input type="file" id="bgImageUpload" accept="image/*" style="margin-top:8px">
            </div>
            <div class="field">
              <label>Link video nền</label>
              <input id="backgroundVideoURL" placeholder="https://.../bg.mp4">
              <input type="file" id="bgVideoUpload" accept="video/*,.mp4,.mov,.m4v,.3gp,.3g2,.mpg,.mpeg,.ts" style="margin-top:8px">
            </div>
          </div>
          <div class="media-preview" id="mediaPreview"><div class="media-preview-empty"><i class="fa-solid fa-image"></i>Xem trước nền trên điện thoại</div></div>
        </div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE APPEARANCE</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-games">
        <div class="card">
          <div class="row-actions">
            <div>
              <h3>Danh sách ứng dụng</h3>
              <p class="sub" style="margin:0">Tên, Bundle ID, icon, URL mở game. AIM/Mod ở trang Danh mục.</p>
            </div>
            <span class="spacer"></span>
            <button class="btn btn-accent btn-sm" id="addGameBtn" type="button"><i class="fa-solid fa-plus"></i> Thêm game</button>
          </div>
          <div id="gamesList"></div>
        </div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE APPLICATIONS</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-catalog">
        <div class="card">
          <div class="row-actions">
            <div>
              <h3>Danh mục</h3>
              <p class="sub" style="margin:0">File .3105 + mật khẩu gói. Bật toggle app = apply / tắt = restore.</p>
            </div>
          </div>
          <div id="catalogList"></div>
        </div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE CATALOG</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-tabs">
        <div class="card">
          <div class="row-actions"><div><h3>Thanh tab theo ứng dụng</h3><p class="sub" style="margin:0">Mỗi ứng dụng có danh sách tab riêng, không dùng chung với ứng dụng khác.</p></div></div>
          <div id="tabsList" class="operation-list"></div>
        </div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE TABS</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-info">
        <div class="card"><h3>Cấu hình Landing Page</h3><p class="sub">Quản lý tên tab trình duyệt, favicon và hai nút tải trên trang chính.</p><div class="grid-2"><div class="field"><label>Tên thương hiệu</label><input id="brandTitle" placeholder="APEX IPA"></div><div class="field"><label>Mô tả</label><input id="brandSubtitle" placeholder="Mô tả ngắn"></div><div class="field wide"><label>Tên trên tab trình duyệt</label><input id="browserTitle" placeholder="APEX IPA — Download"></div><div class="field"><label>Link Get Key</label><input id="getKeyURL" type="url" placeholder="https://.../get-key"></div><div class="field"><label>Link tải file IPA</label><input id="ipaDownloadURL" type="url" placeholder="https://.../APEX.ipa"></div><div class="field"><label>Link favicon</label><input id="faviconURL" type="url" placeholder="https://.../favicon.png"></div><div class="field"><label>Upload favicon</label><input id="faviconUpload" type="file" accept="image/*,.ico"></div><div class="field wide"><label>Link avatar</label><input id="brandAvatarURL" type="url" placeholder="https://.../avatar.png"></div></div></div>
        <div class="card community-section">
          <div class="row-actions">
            <div><h3>Mạng xã hội</h3><p class="sub">Các liên kết xuất hiện dưới phần thiết bị ở trang chủ.</p></div>
            <span class="spacer"></span>
            <button class="btn btn-accent btn-sm" type="button" data-act="add-link" data-kind="socialLinks"><i class="fa-solid fa-plus"></i> Thêm liên kết</button>
          </div>
          <div id="socialLinksList" class="link-list"></div>
        </div>
        <div class="card community-section">
          <div class="row-actions">
            <div><h3>Ghi công</h3><p class="sub">Tên, vai trò và liên kết của người đóng góp.</p></div>
            <span class="spacer"></span>
            <button class="btn btn-accent btn-sm" type="button" data-act="add-link" data-kind="credits"><i class="fa-solid fa-plus"></i> Thêm người</button>
          </div>
          <div id="creditsList" class="link-list"></div>
        </div>
        <div class="card">
          <div class="row-actions"><div><h3>Bảng giá Landing Page</h3><p class="sub">Người xem cuộn xuống dưới màn hình đầu để xem các gói giá.</p></div><span class="spacer"></span><button class="btn btn-accent btn-sm" id="addPricingBtn" type="button"><i class="fa-solid fa-plus"></i> Thêm gói</button></div>
          <div id="pricingPlansList" class="entity-list"></div>
        </div>
        <div class="card"><div class="row-actions"><div><h3>Ảnh PREVIEW</h3><p class="sub">Quản lý nhiều ảnh hiển thị ở trang sau bảng giá.</p></div><span class="spacer"></span><button class="btn btn-accent btn-sm" id="addPreviewBtn" type="button"><i class="fa-solid fa-plus"></i> Thêm ảnh</button></div><div id="previewImagesList" class="entity-list"></div></div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE INFORMATION</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>

      <section class="page" id="page-version">
        <div class="card">
          <h3>Phiên bản đang phát hành</h3>
          <p class="sub">Quản lý bản IPA mới nhất và phiên bản tối thiểu còn được phép sử dụng.</p>
          <div class="grid-2">
            <div class="field"><label>Version mới nhất</label><input id="latestVersion" placeholder="1.0.1"></div>
            <div class="field"><label>Version tối thiểu có thể dùng</label><input id="minimumVersion" placeholder="1.0.1"></div>
            <div class="field wide"><label>Link cập nhật IPA</label><input id="updateURL" type="url" placeholder="https://.../APEX-IPA-[1.0.1].ipa"></div>
            <div class="field wide"><label>Upload file IPA</label><input id="ipaVersionUpload" type="file" accept=".ipa,application/octet-stream"><div class="hint">Tên file nên có dạng APEX-IPA-[1.0.1].ipa. Giới hạn 500 MB.</div></div>
            <div class="field wide"><label>Nội dung bắt buộc cập nhật</label><textarea id="updateMessage" placeholder="Vui lòng cập nhật phiên bản mới nhất để tiếp tục sử dụng."></textarea></div>
          </div>
          <label class="check"><input type="checkbox" id="forceUpdate"> Bắt buộc cập nhật nếu app thấp hơn version tối thiểu</label>
        </div>
        <div class="card">
          <div class="row-actions"><div><h3>Lịch sử phiên bản</h3><p class="sub" style="margin:0">Tự động ghi lại khi đổi version mới nhất hoặc upload IPA; có thể sửa link hay xóa bản cũ.</p></div></div>
          <div id="versionHistoryList" class="entity-list"></div>
        </div>
        <div class="page-save-zone"><button class="page-save" type="button"><span>SAVE VERSION</span><i class="fa-solid fa-arrow-right"></i></button></div>
      </section>
    </div>
  </div>
</div>
<div id="toast"></div>
<div class="editor-modal" id="editorModal" aria-hidden="true">
  <div class="editor-modal-backdrop" data-modal-close></div>
  <section class="editor-modal-panel" role="dialog" aria-modal="true" aria-labelledby="editorModalTitle">
    <header><div><h3 id="editorModalTitle">Chỉnh sửa</h3><p id="editorModalSub"></p></div><button class="modal-close" type="button" data-modal-close aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button></header>
    <form id="editorModalForm"><div class="modal-fields" id="editorModalFields"></div><footer><button class="btn btn-ghost" type="button" data-modal-close>Hủy</button><button class="btn btn-accent" type="submit"><i class="fa-solid fa-floppy-disk"></i> Lưu</button></footer></form>
  </section>
</div>
<div class="image-viewer" id="imageViewer" aria-hidden="true">
  <div class="image-viewer-backdrop" data-preview-close></div>
  <div class="image-viewer-frame">
    <button class="image-viewer-close" type="button" data-preview-close aria-label="Đóng"><i class="fa-solid fa-xmark"></i></button>
    <img id="imageViewerAsset" alt="Ảnh danh mục">
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/gsap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.7/ScrollTrigger.min.js"></script>
<script>
const STORAGE_KEY = 'apex_admin_password_v1';
const PAGE_META = {
  dashboard: { title: 'Dashboard', sub: 'Toàn cảnh dữ liệu và lưu lượng ứng dụng' },
  settings: { title: 'Cài đặt', sub: 'Thông báo, bảo trì và âm thanh' },
  appearance: { title: 'Giao diện', sub: 'Trang hiển thị và ảnh / video nền app' },
  games: { title: 'Ứng dụng', sub: 'Quản lý ứng dụng hiển thị trong app' },
  catalog: { title: 'Danh mục', sub: 'Quản lý danh sách và file .3105' },
  tabs: { title: 'Thanh tab', sub: 'Tên, biểu tượng và thứ tự nhóm trong app' },
  info: { title: 'Landing Page', sub: 'Tên tab, favicon và liên kết tải' },
  version: { title: 'VERSION', sub: 'Phiên bản, link cập nhật và lịch sử IPA' }
};

let password = sessionStorage.getItem(STORAGE_KEY) || '';
let data = emptyData();
let dirty = false;
let saving = false;

const $ = (s, r=document) => r.querySelector(s);
const $$ = (s, r=document) => [...r.querySelectorAll(s)];
const esc = (s) => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

function emptyData(){
  return {
    noticeTitle: '', noticeMessage: '', maintenanceEnabled:false, maintenanceTitle:'MAINTENANCE', maintenanceMessage:'', brandTitle:'APEX IPA', brandSubtitle:'', browserTitle:'', faviconURL:'', brandAvatarURL:'', landingPreviewImageURL:'', getKeyURL:'', ipaDownloadURL:'', latestVersion:'1.0.0', minimumVersion:'1.0.0', forceUpdate:false, updateURL:'', updateMessage:'', versionHistory:[], cardOpacity:0.92,
    musicEnabled: false, musicSource: 'audio', musicURL: '',
    backgroundType: 'none', backgroundImageURL: '', backgroundVideoURL: '', backgroundContentMode: 'cover',
    pageVisibility: { home:true, games:true, patches:true, wallpaper:true, files:true, cleaner:true },
    socialLinks: [], credits: [], pricingPlans: [], landingPreviewImages: [],
    tabs: [{id:'aim',title:'AIM',icon:'scope',order:1},{id:'location',title:'Định vị',icon:'location.north.circle.fill',order:2},{id:'mod',title:'MOD',icon:'gearshape.fill',order:3}],
    games: []
  };
}
function toast(msg, type='ok'){
  const el = $('#toast');
  el.textContent = msg;
  el.className = type;
  el.style.display = 'block';
  void el.offsetWidth;
  el.classList.add('timing');
  clearTimeout(toast._t);
  toast._t = setTimeout(() => { el.style.display = 'none'; el.classList.remove('timing'); }, 2600);
}
function setDirty(v){
  dirty = v;
}
function markDirty(){ setDirty(true); }
function uid(){
  return crypto.randomUUID ? crypto.randomUUID() : ('id-' + Date.now().toString(36) + Math.random().toString(36).slice(2,8));
}
async function api(action, opts={}){
  const headers = Object.assign({}, opts.headers || {});
  const res = await fetch('../api.php?action=' + encodeURIComponent(action), Object.assign({}, opts, { headers, cache: 'no-store' }));
  let json = null; try { json = await res.json(); } catch (_) {}
  if (!res.ok) {
    const err = new Error((json && json.message) || ('HTTP ' + res.status));
    err.status = res.status; throw err;
  }
  return json;
}
function showLogin(errMsg){
  $('#app').classList.add('hidden');
  $('#loginScreen').classList.remove('hidden');
  if (errMsg){ const e=$('#loginError'); e.textContent=errMsg; e.style.display='block'; }
  $('#loginPassword').focus();
}
function showApp(){
  $('#loginScreen').classList.add('hidden');
  $('#app').classList.remove('hidden');
  $('#sessionMeta').textContent = 'Phiên đang hoạt động';
  if (window.gsap) gsap.from('.side-brand,.nav-btn', {x:-18,opacity:0,duration:.55,stagger:.05,ease:'power3.out'});
}
function normalize(raw){
  const d = emptyData();
  Object.assign(d, raw || {});
  if (!Array.isArray(d.games)) d.games = [];
  if (!Array.isArray(d.socialLinks)) d.socialLinks = [];
  if (!Array.isArray(d.credits)) d.credits = [];
  if (!Array.isArray(d.pricingPlans)) d.pricingPlans = [];
  if (!Array.isArray(d.landingPreviewImages)) d.landingPreviewImages = [];
  if (!d.landingPreviewImages.length && d.landingPreviewImageURL) d.landingPreviewImages = [{url:d.landingPreviewImageURL,alt:'Preview',order:1}];
  if (!Array.isArray(d.versionHistory)) d.versionHistory = [];
  d.latestVersion = String(d.latestVersion || '1.0.0').replace(/[\[\]]/g,'');
  d.minimumVersion = String(d.minimumVersion || d.latestVersion).replace(/[\[\]]/g,'');
  d.updateURL = d.updateURL || d.ipaDownloadURL || '';
  d.forceUpdate = !!d.forceUpdate;
  if (!Array.isArray(d.tabs) || !d.tabs.length) d.tabs = emptyData().tabs;
  d.games.forEach(g => {
    if (!Array.isArray(g.items)) g.items = [];
    if (!Array.isArray(g.tabs)) {
      const allowed = Array.isArray(g.tabIDs) ? g.tabIDs : d.tabs.map(t=>t.id);
      g.tabs = d.tabs.filter(t=>allowed.includes(t.id)).map(t=>({...t}));
    }
  });
  d.musicEnabled = !!d.musicEnabled;
  d.musicSource = d.musicSource === 'video' || d.musicUseBackgroundVideo === true ? 'video' : 'audio';
  d.backgroundType = ['none','image','video'].includes(d.backgroundType) ? d.backgroundType : 'none';
  d.backgroundContentMode = d.backgroundContentMode === 'fit' ? 'fit' : 'cover';
  d.musicURL = d.musicURL || '';
  d.backgroundImageURL = d.backgroundImageURL || '';
  d.backgroundVideoURL = d.backgroundVideoURL || '';
  d.cardOpacity = Math.min(1, Math.max(.15, Number(d.cardOpacity) || .92));
  d.pageVisibility = Object.assign({home:true,games:true,patches:true,wallpaper:true,files:true,cleaner:true}, d.pageVisibility || {});
  return d;
}
async function tryLogin(pw){
  password = pw;
  await api('verify', { method: 'POST', body: new URLSearchParams({password}) });
  sessionStorage.setItem(STORAGE_KEY, password);
  showApp();
  await loadData();
}
async function loadData(){
  try { data = normalize(await api('admin_read', { method:'POST', body:new URLSearchParams({password}) })); }
  catch (e) {
    const r = await fetch('../config.php', { cache: 'no-store' });
    if (!r.ok) throw new Error('Không tải được config');
    data = normalize(await r.json());
  }
  bindForms();
  renderGames();
  renderCatalog();
  renderCommunity();
  renderPricing();
  renderPreviewImages();
  renderTabs();
  renderVersions();
  updateStats();
  await loadDashboardStats();
  setDirty(false);
  requestAnimationFrame(setupMotion);
  toast('Đã tải cấu hình');
}

function setupMotion(){
  if (!window.gsap || !window.ScrollTrigger) return;
  gsap.registerPlugin(ScrollTrigger);
  ScrollTrigger.getAll().forEach(t => t.kill());
  const scope = document.querySelector('.page.active');
  if (!scope) return;
  const animatedBlocks = scope.querySelectorAll('.stat,.metric,.card');
  gsap.set(animatedBlocks, {clearProps:'opacity,filter,transform'});
  gsap.from(animatedBlocks, {y:18,duration:.42,stagger:.045,ease:'power2.out',clearProps:'transform'});
  const interactiveRows = Array.from(scope.querySelectorAll('.catalog-group,.entity-row,.field,.row-actions')).filter(el => !el.closest('#gamesList'));
  gsap.set(interactiveRows,{opacity:1,filter:'none',clearProps:'transform'});
  gsap.fromTo(interactiveRows,{y:16,scale:.988},{y:0,scale:1,duration:.46,stagger:.032,ease:'power3.out',clearProps:'transform'});
  const title = document.querySelector('.topbar h2');
  if (title) gsap.fromTo(title,{x:-16},{x:0,duration:.5,ease:'power3.out',clearProps:'transform'});
  if (scope.id === 'page-games') animateGameRows();
  scope.querySelectorAll('.game-block').forEach((card,index) => {
    card.style.zIndex = String(index + 1);
    gsap.fromTo(card,{scale:.94,opacity:.45},{scale:1,opacity:1,ease:'none',scrollTrigger:{trigger:card,start:'top 92%',end:'top 58%',scrub:true}});
  });
  const sub = document.querySelector('#pageSub');
  if (sub && !sub.dataset.split) {
    sub.dataset.split='1';
    sub.innerHTML=sub.textContent.split(' ').map(w=>`<span class="motion-word">${esc(w)} </span>`).join('');
  }
  if (sub) gsap.fromTo(sub.querySelectorAll('.motion-word'),{opacity:.15},{opacity:1,stagger:.08,scrollTrigger:{trigger:'.content',start:'top 80%',end:'top 35%',scrub:true}});
  setupHoverPhysics();
}
function setupHoverPhysics(){
  if (document.body.dataset.hoverPhysics === '1') return;
  document.body.dataset.hoverPhysics='1';
  document.addEventListener('pointerdown',event=>{
    const target=event.target.closest('.btn,.page-save,.catalog-summary');
    if (!target || !window.gsap) return;
    gsap.fromTo(target,{scale:.975},{scale:1,duration:.32,ease:'back.out(2)',clearProps:'transform'});
  });
  document.addEventListener('pointerenter',event=>{
    const target=event.target.closest('.operation-icon,.summary-icon,.visibility-icon');
    if (!target || !window.gsap || event.pointerType==='touch') return;
    gsap.to(target,{rotation:-6,scale:1.08,duration:.28,ease:'power2.out'});
  },true);
  document.addEventListener('pointerleave',event=>{
    const target=event.target.closest('.operation-icon,.summary-icon,.visibility-icon');
    if (!target || !window.gsap || event.pointerType==='touch') return;
    gsap.to(target,{rotation:0,scale:1,duration:.36,ease:'elastic.out(1,.55)',clearProps:'transform'});
  },true);
}
function animateGameRows(){
  const rows = Array.from(document.querySelectorAll('#gamesList .entity-row'));
  if (!rows.length) return;
  if (!window.gsap || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    rows.forEach(row => { row.style.opacity='1'; row.style.filter='none'; });
    return;
  }
  gsap.killTweensOf(rows);
  gsap.set(rows,{opacity:1,filter:'none',clearProps:'transform'});
  gsap.fromTo(rows,{y:24,scale:.975},{y:0,scale:1,duration:.52,stagger:.055,ease:'power3.out',clearProps:'transform'});
}
function bindForms(){
  $('#brandTitle').value = data.brandTitle || 'APEX IPA';
  $('#brandSubtitle').value = data.brandSubtitle || '';
  $('#browserTitle').value = data.browserTitle || '';
  $('#faviconURL').value = data.faviconURL || '';
  $('#brandAvatarURL').value = data.brandAvatarURL || '';
  $('#getKeyURL').value = data.getKeyURL || '';
  $('#ipaDownloadURL').value = data.ipaDownloadURL || '';
  $('#latestVersion').value = data.latestVersion || '1.0.0';
  $('#minimumVersion').value = data.minimumVersion || data.latestVersion || '1.0.0';
  $('#updateURL').value = data.updateURL || data.ipaDownloadURL || '';
  $('#updateMessage').value = data.updateMessage || '';
  $('#forceUpdate').checked = !!data.forceUpdate;
  $('#cardOpacity').value = Math.round((data.cardOpacity || .92) * 100);
  $('#cardOpacityValue').textContent = $('#cardOpacity').value + '%';
  $('#noticeTitle').value = data.noticeTitle || '';
  $('#noticeMessage').value = data.noticeMessage || '';
  $('#maintenanceEnabled').checked = !!data.maintenanceEnabled;
  $('#maintenanceTitle').value = data.maintenanceTitle || 'MAINTENANCE';
  $('#maintenanceMessage').value = data.maintenanceMessage || '';
  $('#musicEnabled').checked = !!data.musicEnabled;
  $('#musicSource').value = data.musicSource || 'audio';
  $('#musicURL').value = data.musicURL || '';
  $('#backgroundType').value = data.backgroundType || 'none';
  $('#backgroundImageURL').value = data.backgroundImageURL || '';
  $('#backgroundVideoURL').value = data.backgroundVideoURL || '';
  $('#backgroundContentMode').value = data.backgroundContentMode || 'cover';
  updateMusicSourceFields();
  $('#pageHomeVisible').checked = data.pageVisibility.home !== false;
  $('#pageGamesVisible').checked = data.pageVisibility.games !== false;
  $('#pagePatchesVisible').checked = data.pageVisibility.patches !== false;
  $('#pageWallpaperVisible').checked = data.pageVisibility.wallpaper !== false;
  $('#pageFilesVisible').checked = data.pageVisibility.files !== false;
  $('#pageCleanerVisible').checked = data.pageVisibility.cleaner !== false;
  updateMediaPreview();
}

function updateMusicSourceFields(){
  const useVideo = $('#musicSource').value === 'video';
  $('#musicAudioFields').classList.toggle('hidden', useVideo);
  $('#musicVideoFields').classList.toggle('hidden', !useVideo);
}

function updateMediaPreview(){
  const root=$('#mediaPreview'); if(!root) return;
  const type=$('#backgroundType').value;
  const imageURL=$('#backgroundImageURL').value.trim();
  const videoURL=$('#backgroundVideoURL').value.trim();
  const mode=$('#backgroundContentMode').value==='fit'?'contain':'cover';
  root.style.setProperty('--media-fit', mode);
  if(type==='image'&&imageURL){root.innerHTML=`<img src="${esc(imageURL)}" alt="Xem trước ảnh nền" onerror="this.parentNode.innerHTML='<div class=&quot;media-preview-empty&quot;><i class=&quot;fa-solid fa-triangle-exclamation&quot;></i>Không tải được ảnh từ URL này</div>'">`;return}
  if(type==='video'&&videoURL){root.innerHTML=`<video src="${esc(videoURL)}" muted autoplay loop playsinline></video>`;return}
  root.innerHTML='<div class="media-preview-empty"><i class="fa-solid fa-image"></i>Chọn ảnh hoặc video để xem trước</div>';
}
function collectForms(){
  data.brandTitle = $('#brandTitle').value.trim();
  data.brandSubtitle = $('#brandSubtitle').value.trim();
  data.browserTitle = $('#browserTitle').value.trim();
  data.faviconURL = $('#faviconURL').value.trim();
  data.brandAvatarURL = $('#brandAvatarURL').value.trim();
  data.getKeyURL = $('#getKeyURL').value.trim();
  data.ipaDownloadURL = $('#ipaDownloadURL').value.trim();
  data.latestVersion = $('#latestVersion').value.trim().replace(/[\[\]]/g,'');
  data.minimumVersion = $('#minimumVersion').value.trim().replace(/[\[\]]/g,'');
  data.updateURL = $('#updateURL').value.trim();
  data.updateMessage = $('#updateMessage').value.trim();
  data.forceUpdate = $('#forceUpdate').checked;
  if (data.updateURL) data.ipaDownloadURL = data.updateURL;
  syncCurrentVersionHistory();
  data.cardOpacity = (+$('#cardOpacity').value || 92) / 100;
  data.noticeTitle = $('#noticeTitle').value;
  data.noticeMessage = $('#noticeMessage').value;
  data.maintenanceEnabled = $('#maintenanceEnabled').checked;
  data.maintenanceTitle = $('#maintenanceTitle').value.trim();
  data.maintenanceMessage = $('#maintenanceMessage').value;
  data.musicEnabled = $('#musicEnabled').checked;
  data.musicSource = $('#musicSource').value;
  data.musicUseBackgroundVideo = data.musicSource === 'video';
  data.musicURL = $('#musicURL').value.trim();
  delete data.musicFromVideoURL;
  data.backgroundType = $('#backgroundType').value;
  data.backgroundImageURL = $('#backgroundImageURL').value.trim();
  data.backgroundVideoURL = $('#backgroundVideoURL').value.trim();
  data.backgroundContentMode = $('#backgroundContentMode').value;
  data.pageVisibility = {
    home: $('#pageHomeVisible').checked,
    games: $('#pageGamesVisible').checked,
    patches: $('#pagePatchesVisible').checked,
    wallpaper: $('#pageWallpaperVisible').checked,
    files: $('#pageFilesVisible').checked,
    cleaner: $('#pageCleanerVisible').checked
  };
}
function updateStats(){
  const games = data.games || [];
  $('#statGames').textContent = String(games.length);
  $('#statItems').textContent = String(games.reduce((n,g)=>n+((g.items||[]).length),0));
  $('#statTabs').textContent = String(games.reduce((n,g)=>n+((g.tabs||[]).length),0));
}
function compactNumber(value){
  const n=Number(value)||0;
  return n<1000?String(n):new Intl.NumberFormat('en',{notation:'compact',maximumFractionDigits:1}).format(n).toUpperCase();
}
async function loadDashboardStats(){
  const state=$('#dashboardState'); if(state) state.textContent='Đang đồng bộ';
  try{
    const result=await api('admin_stats',{method:'POST',body:new URLSearchParams({password})});
    const stats=result.requests||{};
    $('#statRequestsTotal').textContent=compactNumber(stats.total);
    $('#statRequestsToday').textContent=compactNumber(stats.today);
    $('#statRequestsWeek').textContent=compactNumber(stats.last7);
    if(state) state.textContent='SQL đang hoạt động';
  }catch(e){if(state)state.textContent='Không đọc được thống kê';}
}
function gameTabs(gi){ return data.games[gi]?.tabs || []; }
function catLabel(gi,c){ return (gameTabs(gi).find(tab=>tab.id===c)||{}).title || c; }
function categoryOptions(gi,selected){ return [...gameTabs(gi)].sort((a,b)=>(+a.order||0)-(+b.order||0)).map(tab=>`<option value="${esc(tab.id)}" ${tab.id===selected?'selected':''}>${esc(tab.title)}</option>`).join(''); }

function renderGames(){
  const root = $('#gamesList');
  if (!data.games.length){ root.innerHTML = `<div class="empty">Chưa có game. Bấm “+ Thêm game”.</div>`; return; }
  root.className='entity-list';
  root.innerHTML=[...data.games].sort((a,b)=>(+a.order||9999)-(+b.order||9999)).map(g=>{const gi=data.games.indexOf(g);return `<div class="entity-row"><span class="operation-icon"><i class="fa-solid fa-gamepad"></i></span><span class="entity-copy"><strong>${esc(g.name||'Game')}</strong><small>${esc(g.bundleID||'Chưa có Bundle ID')} · ${(g.items||[]).length} mục</small></span><span class="order-badge">#${esc(g.order??gi+1)}</span><span class="pill">GAME</span><span class="entity-actions"><button class="btn btn-ghost btn-sm" data-act="edit-game" data-gi="${gi}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-game" data-gi="${gi}"><i class="fa-solid fa-trash"></i> Xóa</button></span></div>`}).join('');
  if (document.querySelector('#page-games.active')) requestAnimationFrame(animateGameRows);
}
function renderCatalog(){
  const root = $('#catalogList');
  if (!data.games.length){ root.innerHTML = `<div class="empty">Chưa có game. Thêm ở trang GAME trước.</div>`; return; }
  root.innerHTML = [...data.games].sort((a,b)=>(+a.order||9999)-(+b.order||9999)).map(g => [g,data.games.indexOf(g)]).map(([g,gi]) => `
    <details class="catalog-group" ${gi===0?'open':''}>
      <summary class="catalog-summary">
        <span class="summary-icon"><i class="fa-solid fa-gamepad"></i></span>
        <span class="catalog-item-title"><strong>${esc(g.name||'Game')}</strong><small>${esc(g.bundleID||'Chưa có Bundle ID')}</small></span>
        <span class="pill">${(g.items||[]).length} mục</span>
        <i class="fa-solid fa-chevron-down chevron"></i>
      </summary>
      <div class="catalog-body">
        <div class="row-actions" style="padding:4px 4px 10px"><span class="spacer"></span><button class="btn btn-accent btn-sm" type="button" data-act="add-item" data-gi="${gi}"><i class="fa-solid fa-plus"></i> Thêm mục</button></div>
        ${(g.items||[]).length ? `<div class="entity-list">${[...g.items].sort((a,b)=>(+a.order||9999)-(+b.order||9999)).map(it=>itemHTML(gi,it,g.items.indexOf(it))).join('')}</div>` : `<div class="empty" style="padding:16px">Chưa có mục trong game này.</div>`}
      </div>
    </details>`).join('');
}
function itemHTML(gi, it, ii){
  const hasFile = !!(it.fileURL && String(it.fileURL).trim());
  const media = it.imageURL ? `<button class="catalog-thumb" type="button" data-preview="${esc(it.imageURL)}" aria-label="Xem ảnh ${esc(it.name||'')}"><img src="${esc(it.imageURL)}" alt=""></button>` : `<span class="catalog-thumb empty-thumb"><i class="fa-solid fa-image"></i></span>`;
  return `<div class="entity-row media-row">${media}<span class="entity-copy"><strong>${esc(it.name||'Mục chưa đặt tên')}</strong><small>${esc(catLabel(gi,it.category))} · ${hasFile?'Đã gắn file .3105':'Chưa có file'}</small></span><span class="order-badge">#${esc(it.order??ii+1)}</span><span class="pill ${hasFile?'ok':'warn'}">${hasFile?'Sẵn sàng':'Thiếu file'}</span><span class="entity-actions"><button class="btn btn-ghost btn-sm" data-act="edit-item" data-gi="${gi}" data-ii="${ii}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-item" data-gi="${gi}" data-ii="${ii}"><i class="fa-solid fa-trash"></i> Xóa</button></span></div>`;
}
function renderTabs(){
  const root=$('#tabsList'); if(!root) return;
  if(!data.games.length){root.innerHTML='<div class="empty">Hãy thêm ứng dụng trước.</div>';return;}
  root.innerHTML=[...data.games].sort((a,b)=>(+a.order||9999)-(+b.order||9999)).map(g=>{const gi=data.games.indexOf(g),tabs=gameTabs(gi);return `<details class="catalog-group" open><summary class="catalog-summary"><span class="summary-icon"><i class="fa-solid fa-mobile-screen"></i></span><span class="catalog-item-title"><strong>${esc(g.name)}</strong><small>${esc(g.bundleID)}</small></span><span class="pill">${tabs.length} tab</span><i class="fa-solid fa-chevron-down chevron"></i></summary><div class="catalog-body"><div class="row-actions" style="padding:4px 4px 10px"><span class="spacer"></span><button class="btn btn-accent btn-sm" data-act="add-tab" data-gi="${gi}"><i class="fa-solid fa-plus"></i> Thêm tab</button></div>${tabs.length?`<div class="entity-list">${[...tabs].sort((a,b)=>(+a.order||0)-(+b.order||0)).map(tab=>{const ti=tabs.indexOf(tab);return `<div class="entity-row"><span class="operation-icon"><i class="fa-solid fa-table-columns"></i></span><span class="entity-copy"><strong>${esc(tab.title)}</strong><small>${esc(tab.id)} · ${esc(tab.icon)}</small></span><span class="order-badge">#${esc(tab.order)}</span><span class="entity-actions"><button class="btn btn-ghost btn-sm" data-act="edit-tab" data-gi="${gi}" data-ti="${ti}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-tab" data-gi="${gi}" data-ti="${ti}"><i class="fa-solid fa-trash"></i> Xóa</button></span></div>`}).join('')}</div>`:'<div class="empty compact">Ứng dụng này chưa có tab.</div>'}</div></details>`}).join('');
}
function renderCommunity(){
  renderLinkList('socialLinks', '#socialLinksList');
  renderLinkList('credits', '#creditsList');
}

function renderPricing(){
  const root=$('#pricingPlansList');if(!root)return;
  const rows=data.pricingPlans||[];
  root.innerHTML=rows.length?rows.map((item,index)=>`<div class="entity-row"><span class="operation-icon"><i class="fa-solid fa-tags"></i></span><span class="entity-copy"><strong>${esc(item.name||'Gói chưa đặt tên')} · ${esc(item.price||'Liên hệ')}</strong><small>${esc(item.description||'')} · ${(item.features||[]).length} quyền lợi</small></span><span class="order-badge">#${esc(item.order??index+1)}</span><span class="entity-actions"><button class="btn btn-ghost btn-sm" data-act="edit-pricing" data-pi="${index}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-pricing" data-pi="${index}"><i class="fa-solid fa-trash"></i> Xóa</button></span></div>`).join(''):'<div class="empty compact">Chưa có gói giá. Bấm “Thêm gói”.</div>';
}

function openPricingEditor(index=null){
  const list=data.pricingPlans||(data.pricingPlans=[]),item=index===null?{name:'',price:'',description:'',features:[],buttonText:'MUA NGAY',url:'',featured:false,order:list.length+1}:list[index];
  const features=Array.isArray(item.features)?item.features.join('\n'):String(item.features||'');
  const fields=fieldHTML('Tên gói','name',item.name)+fieldHTML('Giá','price',item.price)+fieldHTML('Mô tả','description',item.description,'text',true)+`<div class="field wide"><label>Quyền lợi (mỗi dòng một mục)</label><textarea name="features">${esc(features)}</textarea></div>`+fieldHTML('Chữ trên nút','buttonText',item.buttonText||'MUA NGAY')+fieldHTML('Link mua','url',item.url,'url')+fieldHTML('Thứ tự','order',item.order??1,'number')+`<div class="field wide"><label class="check"><input name="featured" type="checkbox" ${item.featured?'checked':''}> Gói nổi bật</label></div>`;
  openEditor(index===null?'Thêm gói giá':'Sửa gói giá','Hiển thị ở phần bảng giá trên landing page',fields,fd=>{Object.assign(item,{name:String(fd.get('name')||'').trim(),price:String(fd.get('price')||'').trim(),description:String(fd.get('description')||'').trim(),features:String(fd.get('features')||'').split(/\r?\n/).map(v=>v.trim()).filter(Boolean),buttonText:String(fd.get('buttonText')||'MUA NGAY').trim(),url:String(fd.get('url')||'').trim(),order:+fd.get('order')||0,featured:fd.get('featured')==='on'});if(index===null)list.push(item);renderPricing();markDirty();});
}

function renderPreviewImages(){
  const root=$('#previewImagesList');if(!root)return;
  const rows=data.landingPreviewImages||[];
  root.innerHTML=rows.length?[...rows].sort((a,b)=>(+a.order||9999)-(+b.order||9999)).map(item=>{const index=rows.indexOf(item);return `<div class="entity-row media-row"><button class="catalog-thumb" type="button" data-preview="${esc(item.url||'')}" aria-label="Xem ảnh Preview"><img src="${esc(item.url||'')}" alt=""></button><span class="entity-copy"><strong>${esc(item.alt||'Preview')}</strong><small>${esc(item.url||'Chưa có ảnh')}</small></span><span class="order-badge">#${esc(item.order??index+1)}</span><span class="entity-actions"><button class="btn btn-ghost btn-sm" data-act="edit-preview" data-pri="${index}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-preview" data-pri="${index}"><i class="fa-solid fa-trash"></i> Xóa</button></span></div>`}).join(''):'<div class="empty compact">Chưa có ảnh Preview. Bấm “Thêm ảnh”.</div>';
}

function openPreviewEditor(index=null){
  const list=data.landingPreviewImages||(data.landingPreviewImages=[]),item=index===null?{url:'',alt:'Preview',order:list.length+1}:list[index];
  const fields=fieldHTML('Link ảnh','url',item.url||'','url',true)+fieldHTML('Mô tả ảnh','alt',item.alt||'Preview')+fieldHTML('Thứ tự','order',item.order??1,'number')+`<div class="field wide media-upload-field"><label>Upload ảnh</label><input name="previewFile" type="file" accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.avif"><small>Có thể dùng link hoặc upload trực tiếp.</small></div>`;
  openEditor(index===null?'Thêm ảnh Preview':'Sửa ảnh Preview','Hiển thị trong gallery PREVIEW sau bảng giá',fields,async fd=>{Object.assign(item,{url:String(fd.get('url')||'').trim(),alt:String(fd.get('alt')||'Preview').trim(),order:+fd.get('order')||0});if(index===null){list.push(item);index=list.length-1;}const file=fd.get('previewFile');if(file&&file.size)await uploadFile(file,'landingPreview',index);renderPreviewImages();markDirty();});
}

function renderVersions(){
  const root=$('#versionHistoryList'); if(!root)return;
  const rows=[...(data.versionHistory||[])];
  if(data.latestVersion&&!rows.some(item=>item.version===data.latestVersion))rows.push({version:data.latestVersion,url:data.updateURL||data.ipaDownloadURL||'',publishedAt:'Chưa lưu',virtual:true});
  rows.sort((a,b)=>String(b.version).localeCompare(String(a.version),undefined,{numeric:true}));
  root.innerHTML=rows.length?rows.map(item=>{const index=data.versionHistory.indexOf(item),actions=item.virtual?'':`<button class="btn btn-ghost btn-sm" data-act="edit-version" data-vi="${index}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-version" data-vi="${index}"><i class="fa-solid fa-trash"></i> Xóa</button>`;return `<div class="entity-row"><span class="operation-icon"><i class="fa-solid fa-box-archive"></i></span><span class="entity-copy"><strong>[${esc(item.version||'0.0.0')}]</strong><small>${esc(item.url||'Chưa có link IPA')} · ${esc(item.publishedAt||'')}</small></span><span class="pill ${item.version===data.latestVersion?'ok':''}">${item.version===data.latestVersion?'MỚI NHẤT':'LỊCH SỬ'}</span><span class="entity-actions">${actions}</span></div>`}).join(''):'<div class="empty compact">Chưa có version nào.</div>';
}

function syncCurrentVersionHistory(){
  const version=String(data.latestVersion||'').trim().replace(/[\[\]]/g,'');
  if(!/^\d+\.\d+\.\d+$/.test(version))return;
  const list=data.versionHistory||(data.versionHistory=[]);
  let item=list.find(entry=>entry.version===version);
  if(!item){item={version,url:'',publishedAt:new Date().toISOString().slice(0,10)};list.push(item);}
  if(data.updateURL)item.url=data.updateURL;
  if(!item.publishedAt)item.publishedAt=new Date().toISOString().slice(0,10);
}

function openVersionEditor(index=null){
  const list=data.versionHistory||(data.versionHistory=[]);
  const item=index===null?{version:data.latestVersion||'1.0.0',url:data.updateURL||'',publishedAt:new Date().toISOString().slice(0,10)}:list[index];
  const fields=fieldHTML('Version','version',item.version)+fieldHTML('Link IPA','url',item.url,'url',true)+fieldHTML('Ngày phát hành','publishedAt',item.publishedAt||'');
  openEditor(index===null?'Thêm version':'Sửa version','Tên hiển thị tự động có dạng [1.0.1]',fields,fd=>{
    const version=String(fd.get('version')||'').trim().replace(/[\[\]]/g,'');
    if(!/^\d+\.\d+\.\d+$/.test(version)){toast('Version phải có dạng 1.0.1','err');return false;}
    Object.assign(item,{version,url:String(fd.get('url')||'').trim(),publishedAt:String(fd.get('publishedAt')||'').trim()});
    if(index===null)list.push(item);
    renderVersions();markDirty();
  });
}
function renderLinkList(kind, selector){
  const root=$(selector), rows=data[kind]||[];
  root.className='entity-list';
  root.innerHTML=rows.length ? rows.map((item,index)=>`<div class="entity-row"><span class="operation-icon"><i class="fa-solid fa-link"></i></span><span class="entity-copy"><strong>${esc(item.name)}</strong><small>${esc(item.role||'Chưa có mô tả')} · ${esc(item.url||'Chưa có liên kết')}</small></span><span class="entity-actions"><button class="btn btn-ghost btn-sm" data-act="edit-link" data-kind="${kind}" data-li="${index}"><i class="fa-solid fa-pen"></i> Sửa</button><button class="btn btn-danger btn-sm" data-act="del-link" data-kind="${kind}" data-li="${index}"><i class="fa-solid fa-trash"></i> Xóa</button></span></div>`).join('') : '<div class="empty compact">Chưa có dữ liệu.</div>';
}
let modalSubmit = null;
function fieldHTML(label,name,value='',type='text',wide=false,extra=''){
  return `<div class="field ${wide?'wide':''}"><label>${esc(label)}</label><input name="${esc(name)}" type="${esc(type)}" value="${esc(value)}" ${extra}></div>`;
}
function openEditor(title,sub,fields,onSubmit){
  $('#editorModalTitle').textContent=title; $('#editorModalSub').textContent=sub||''; $('#editorModalFields').innerHTML=fields; modalSubmit=onSubmit;
  $('#editorModal').classList.add('open'); $('#editorModal').setAttribute('aria-hidden','false'); document.body.style.overflow='hidden';
  requestAnimationFrame(()=>{if(window.gsap)gsap.fromTo('.editor-modal-panel',{y:18,scale:.97,opacity:0},{y:0,scale:1,opacity:1,duration:.24,ease:'power2.out'});$('#editorModalFields input, #editorModalFields select')[0]?.focus();});
}
function closeEditor(){ $('#editorModal').classList.remove('open'); $('#editorModal').setAttribute('aria-hidden','true'); document.body.style.overflow=''; modalSubmit=null; }
function openGameEditor(index=null){
  const item=index===null?{id:uid(),name:'',bundleID:'',iconURL:'',launchURL:'',order:data.games.length+1,items:[],tabs:[]}:data.games[index];
  const fields=fieldHTML('Tên ứng dụng','name',item.name)+fieldHTML('Bundle ID','bundleID',item.bundleID)+fieldHTML('URL icon','iconURL',item.iconURL,'url',true)+fieldHTML('URL mở ứng dụng','launchURL',item.launchURL,'text',true)+fieldHTML('Thứ tự','order',item.order??1,'number');
  openEditor(index===null?'Thêm ứng dụng':'Chỉnh sửa ứng dụng','Thông tin hiển thị trong ứng dụng',fields,fd=>{Object.assign(item,{name:fd.get('name'),bundleID:fd.get('bundleID'),iconURL:fd.get('iconURL'),launchURL:fd.get('launchURL'),order:+fd.get('order')||0});if(index===null)data.games.push(item);renderGames();renderCatalog();renderTabs();updateStats();markDirty();});
}
function openItemEditor(gi,ii=null){
  const tabs=gameTabs(gi); if(!tabs.length){toast('Hãy tạo ít nhất một tab cho ứng dụng này','err');return;}
  const list=data.games[gi].items||(data.games[gi].items=[]), item=ii===null?{id:uid(),name:'',subtitle:'',category:tabs[0].id,order:list.length+1,imageURL:'',fileURL:'',packagePassword:'',enabled:false}:list[ii];
  const options=categoryOptions(gi,item.category), pw=item.packagePassword??item.password??'';
  const fields=fieldHTML('Tên danh mục','name',item.name)+`<div class="field"><label>Thanh tab</label><select name="category">${options}</select></div>`+fieldHTML('Thứ tự','order',item.order??1,'number')+fieldHTML('Mật khẩu .3105','packagePassword',pw,'password')+fieldHTML('URL hình ảnh','imageURL',item.imageURL||'','url',true)+`<div class="field wide media-upload-field"><label>Upload hình ảnh</label><input name="imageFile" type="file" accept="image/*,.jpg,.jpeg,.png,.webp,.gif,.heic,.heif,.avif"><small>Ảnh xuất hiện bên trong mục; chạm ảnh trên app để xem lớn.</small></div>`+fieldHTML('URL file .3105','fileURL',item.fileURL||'','url',true)+`<div class="field wide"><label>Upload file .3105</label><input name="packageFile" type="file" accept=".3105,application/octet-stream"></div>`;
  openEditor(ii===null?'Thêm danh mục':'Chỉnh sửa danh mục',data.games[gi].name,fields,async fd=>{Object.assign(item,{name:fd.get('name'),category:fd.get('category'),order:+fd.get('order')||0,imageURL:fd.get('imageURL'),packagePassword:fd.get('packagePassword'),fileURL:fd.get('fileURL')});if(ii===null){list.push(item);ii=list.length-1;}const image=fd.get('imageFile');if(image&&image.size)await uploadFile(image,'itemImage',gi,ii);const file=fd.get('packageFile');if(file&&file.size)await uploadFile(file,'item',gi,ii);renderCatalog();updateStats();markDirty();});
}
function openTabEditor(gi,index=null){
  const tabs=gameTabs(gi), tab=index===null?{id:'tab-'+Date.now().toString(36),title:'',icon:'square.grid.2x2.fill',order:tabs.length+1}:tabs[index], oldID=tab.id;
  const fields=fieldHTML('Tên tab','title',tab.title)+fieldHTML('ID','id',tab.id)+fieldHTML('SF Symbol','icon',tab.icon)+fieldHTML('Thứ tự','order',tab.order,'number');
  openEditor(index===null?'Thêm tab':'Chỉnh sửa tab',`Thanh tab riêng của ${data.games[gi].name}`,fields,fd=>{const newID=String(fd.get('id')).trim();if(!newID){toast('ID tab không được để trống','err');return false;}if(tabs.some((t,i)=>i!==index&&t.id===newID)){toast('ID tab đã tồn tại trong ứng dụng này','err');return false;}if(index!==null&&newID!==oldID)(data.games[gi].items||[]).forEach(it=>{if(it.category===oldID)it.category=newID;});Object.assign(tab,{title:fd.get('title'),id:newID,icon:fd.get('icon'),order:+fd.get('order')||0});if(index===null)tabs.push(tab);renderTabs();renderCatalog();markDirty();});
}
function openLinkEditor(kind,index=null){
  const list=data[kind]||(data[kind]=[]), item=index===null?{id:uid(),name:'',role:'',url:'',appIcon:'link'}:list[index];
  const fields=fieldHTML('Tên','name',item.name)+fieldHTML('Vai trò / mô tả','role',item.role)+fieldHTML('Đường dẫn','url',item.url,'url',true)+fieldHTML('SF Symbol trong app','appIcon',item.appIcon||'link');
  openEditor(index===null?'Thêm mục':'Chỉnh sửa mục',kind==='credits'?'Ghi công':'Mạng xã hội',fields,fd=>{Object.assign(item,{name:fd.get('name'),role:fd.get('role'),url:fd.get('url'),appIcon:fd.get('appIcon'),icon:''});if(index===null)list.push(item);renderCommunity();markDirty();});
}
function setPage(name){
  $$('.nav-btn').forEach(b => b.classList.toggle('active', b.dataset.page === name));
  $$('.page').forEach(p => p.classList.toggle('active', p.id === 'page-' + name));
  const meta = PAGE_META[name] || PAGE_META.dashboard;
  $('#pageTitle').textContent = meta.title;
  $('#pageSub').textContent = meta.sub;
  delete $('#pageSub').dataset.split;
  closeMobile();
  requestAnimationFrame(setupMotion);
}
function closeMobile(){ $('#sidebar').classList.remove('open'); $('#backdrop').classList.remove('show'); }

async function saveAll(event){
  if (saving) return;
  saving = true;
  const saveButton = event?.currentTarget || $('.page.active .page-save');
  if (saveButton) saveButton.disabled = true;
  collectForms();
  (data.games||[]).forEach(g => {
    (g.items||[]).forEach(it => {
      if (it.password != null && !it.packagePassword) it.packagePassword = it.password;
      delete it.password;
      if (it.packagePassword == null) it.packagePassword = '';
    });
  });
  try {
    const bytes = new TextEncoder().encode(JSON.stringify(data));
    let binary = '';
    for (let i=0; i<bytes.length; i+=0x8000) binary += String.fromCharCode(...bytes.subarray(i,i+0x8000));
    const body = new URLSearchParams({password, payload_base64:btoa(binary)});
    await api('save', { method:'POST', body });
    setDirty(false); updateStats(); renderVersions(); toast('Đã lưu trang hiện tại','ok');
  } catch (e) { toast(e.message || 'Lỗi lưu','err'); }
  finally { saving=false; if(saveButton) saveButton.disabled=false; }
}
async function uploadFile(file, kind, gi, ii){
  const fd = new FormData();
  fd.append('file', file);
  fd.append('password', password);
  try {
    // Authenticate outside the multipart body. PHP can discard the entire body
    // (including its password field) when the file exceeds post_max_size.
    const j = await api('upload', {
      method:'POST',
      headers:{'X-Admin-Password':password},
      body:fd
    });
    if (!j.ok) throw new Error(j.message || 'Upload lỗi');
    const url = new URL(j.url, location.href).href;
    if (kind === 'item') {
      data.games[gi].items[ii].fileURL = url;
      renderCatalog();
    } else if (kind === 'itemImage') {
      data.games[gi].items[ii].imageURL = url;
      renderCatalog();
    } else if (kind === 'music') {
      data.musicURL = url; $('#musicURL').value = url;
    } else if (kind === 'favicon') {
      data.faviconURL = url; $('#faviconURL').value = url;
    } else if (kind === 'landingPreview') {
      if (data.landingPreviewImages[gi]) data.landingPreviewImages[gi].url = url;
    } else if (kind === 'bgImage') {
      data.backgroundImageURL = url; $('#backgroundImageURL').value = url;
      if ($('#backgroundType').value === 'none') { $('#backgroundType').value = 'image'; data.backgroundType = 'image'; }
    } else if (kind === 'bgVideo') {
      data.backgroundVideoURL = url; $('#backgroundVideoURL').value = url;
      if ($('#backgroundType').value === 'none') { $('#backgroundType').value = 'video'; data.backgroundType = 'video'; }
    } else if (kind === 'ipaVersion') {
      const version=$('#latestVersion').value.trim().replace(/[\[\]]/g,'') || data.latestVersion || '1.0.0';
      data.latestVersion=version;
      data.updateURL=url;
      data.ipaDownloadURL=url;
      $('#updateURL').value=url;
      $('#ipaDownloadURL').value=url;
      const existing=(data.versionHistory||[]).find(v=>v.version===version);
      if(existing){existing.url=url;existing.publishedAt=new Date().toISOString().slice(0,10);}
      else{(data.versionHistory||(data.versionHistory=[])).push({version,url,publishedAt:new Date().toISOString().slice(0,10)});}
      renderVersions();
    }
    updateMediaPreview();
    markDirty();
    if (kind === 'bgImage' || kind === 'bgVideo') {
      await saveAll();
      toast('Đã upload và áp dụng nền');
    } else {
      toast('Đã upload');
    }
  } catch (e) { toast(e.message || 'Upload lỗi','err'); }
}

$('#loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const pw = $('#loginPassword').value.trim();
  const btn = $('#loginBtn'); btn.disabled = true; $('#loginError').style.display = 'none';
  try { await tryLogin(pw); }
  catch (err) { password=''; sessionStorage.removeItem(STORAGE_KEY); showLogin(err.message||'Sai mật khẩu'); }
  finally { btn.disabled = false; }
});
$('#logoutBtn').addEventListener('click', () => { password=''; sessionStorage.removeItem(STORAGE_KEY); showLogin(); $('#loginPassword').value=''; });
$('#reloadBtn').addEventListener('click', async () => { try { await loadData(); } catch(e){ toast(e.message||'Tải lỗi','err'); } });
$$('.page-save').forEach(button => button.addEventListener('click', saveAll));
$('#addGameBtn').addEventListener('click', () => openGameEditor());
$('#addPricingBtn').addEventListener('click', () => openPricingEditor());
$('#addPreviewBtn').addEventListener('click', () => openPreviewEditor());
$('#dashboardReload').addEventListener('click', async () => { await loadData(); toast('Đã làm mới Dashboard'); });
$('#editorModalForm').addEventListener('submit',async e=>{e.preventDefault();if(!modalSubmit)return;const result=await modalSubmit(new FormData(e.currentTarget));if(result!==false)closeEditor();});
$$('[data-modal-close]').forEach(el=>el.addEventListener('click',closeEditor));
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&$('#editorModal').classList.contains('open'))closeEditor();});
document.addEventListener('keydown',e=>{if(e.key==='Escape'&&$('#imageViewer').classList.contains('open')){$('#imageViewer').classList.remove('open');$('#imageViewer').setAttribute('aria-hidden','true');$('#imageViewerAsset').removeAttribute('src');}});
$$('.nav-btn').forEach(btn => btn.addEventListener('click', () => setPage(btn.dataset.page)));
$('#menuBtn').addEventListener('click', () => { $('#sidebar').classList.add('open'); $('#backdrop').classList.add('show'); });
$('#backdrop').addEventListener('click', closeMobile);

['brandTitle','brandSubtitle','browserTitle','faviconURL','brandAvatarURL','getKeyURL','ipaDownloadURL','latestVersion','minimumVersion','updateURL','updateMessage','noticeTitle','noticeMessage','maintenanceTitle','maintenanceMessage','musicURL','backgroundImageURL','backgroundVideoURL'].forEach(id => {
  $('#'+id).addEventListener('input', () => { markDirty(); if(id==='backgroundImageURL'||id==='backgroundVideoURL') updateMediaPreview(); });
});
$('#musicEnabled').addEventListener('change', markDirty);
$('#maintenanceEnabled').addEventListener('change', markDirty);
$('#forceUpdate').addEventListener('change', markDirty);
$('#musicSource').addEventListener('change', () => { updateMusicSourceFields(); markDirty(); });
$('#backgroundType').addEventListener('change', () => { markDirty(); updateMediaPreview(); });
$('#backgroundContentMode').addEventListener('change', () => { markDirty(); updateMediaPreview(); });
$('#cardOpacity').addEventListener('input', e => { $('#cardOpacityValue').textContent=e.target.value+'%'; markDirty(); });
['pageHomeVisible','pageGamesVisible','pagePatchesVisible','pageWallpaperVisible','pageFilesVisible','pageCleanerVisible'].forEach(id => $('#'+id).addEventListener('change', markDirty));

$('#musicUpload').addEventListener('change', e => { const f=e.target.files&&e.target.files[0]; if(f) uploadFile(f,'music'); });
$('#faviconUpload').addEventListener('change', e => { const f=e.target.files&&e.target.files[0]; if(f) uploadFile(f,'favicon'); });
$('#bgImageUpload').addEventListener('change', e => { const f=e.target.files&&e.target.files[0]; if(f) uploadFile(f,'bgImage'); });
$('#bgVideoUpload').addEventListener('change', e => { const f=e.target.files&&e.target.files[0]; if(f) uploadFile(f,'bgVideo'); });
$('#ipaVersionUpload').addEventListener('change', e => { const f=e.target.files&&e.target.files[0]; if(f) uploadFile(f,'ipaVersion'); });

document.addEventListener('input', (e) => {
  const t = e.target;
  if (t.matches('input[data-g][data-k]')) {
    const gi=+t.dataset.g, k=t.dataset.k;
    if (data.games[gi]) { data.games[gi][k]=k==='order'?+t.value:t.value; markDirty(); }
  }
  if (t.matches('input[data-gi][data-ii][data-k], select[data-gi][data-ii][data-k]')) {
    const gi=+t.dataset.gi, ii=+t.dataset.ii, k=t.dataset.k;
    const it = data.games[gi] && data.games[gi].items[ii];
    if (it) {
      it[k]=k==='order'?+t.value:t.value; markDirty();
      if (k==='category'){ const head=t.closest('.item-block')?.querySelector('.pill'); if(head) head.textContent=catLabel(gi,t.value); }
    }
  }
});
$('#latestVersion').addEventListener('change',e=>{data.latestVersion=e.target.value.trim().replace(/[\[\]]/g,'');data.updateURL=$('#updateURL').value.trim();syncCurrentVersionHistory();renderVersions();markDirty();});
document.addEventListener('click', async (e) => {
  const preview = e.target.closest('[data-preview]');
  if (preview) {
    $('#imageViewerAsset').src=preview.dataset.preview;
    $('#imageViewer').classList.add('open');
    $('#imageViewer').setAttribute('aria-hidden','false');
    if(window.gsap)gsap.fromTo('#imageViewerAsset',{scale:.82,opacity:0},{scale:1,opacity:1,duration:.45,ease:'power3.out'});
    return;
  }
  if (e.target.closest('[data-preview-close]')) {
    $('#imageViewer').classList.remove('open');
    $('#imageViewer').setAttribute('aria-hidden','true');
    $('#imageViewerAsset').removeAttribute('src');
    return;
  }
  const btn = e.target.closest('[data-act]'); if (!btn) return;
  const act=btn.dataset.act, gi=+btn.dataset.gi, ii=+btn.dataset.ii;
  if (act==='del-game') {
    if (!confirm('Xóa game này?')) return;
    data.games.splice(gi,1); renderGames(); renderCatalog(); renderTabs(); updateStats(); markDirty();
  }
  if (act==='edit-game') openGameEditor(gi);
  if (act==='add-item') {
    openItemEditor(gi);
  }
  if (act==='edit-item') openItemEditor(gi,ii);
  if (act==='del-item') {
    data.games[gi].items.splice(ii,1); renderCatalog(); updateStats(); markDirty();
  }
  if (act==='add-tab') {
    openTabEditor(gi);
  }
  if (act==='edit-tab') openTabEditor(gi,+btn.dataset.ti);
  if (act==='del-tab') {
    const index=+btn.dataset.ti, tabs=gameTabs(gi), tab=tabs[index];
    if(!tab || (data.games[gi].items||[]).some(it=>it.category===tab.id)){toast('Không thể xóa tab đang có danh mục','err');return;}
    if(!confirm('Xóa tab này?')) return; tabs.splice(index,1); renderTabs(); renderCatalog(); markDirty();
  }
  if (act==='add-link') {
    openLinkEditor(btn.dataset.kind);
  }
  if (act==='edit-link') openLinkEditor(btn.dataset.kind,+btn.dataset.li);
  if (act==='del-link') {
    const kind=btn.dataset.kind, index=+btn.dataset.li;
    if (!confirm('Xóa mục này?')) return;
    data[kind].splice(index,1); renderCommunity(); markDirty();
  }
  if(act==='edit-version')openVersionEditor(+btn.dataset.vi);
  if(act==='del-version'){
    const index=+btn.dataset.vi;if(!confirm('Xóa version này khỏi lịch sử?'))return;
    data.versionHistory.splice(index,1);renderVersions();markDirty();
  }
  if(act==='edit-pricing')openPricingEditor(+btn.dataset.pi);
  if(act==='del-pricing'){
    const index=+btn.dataset.pi;if(!confirm('Xóa gói giá này?'))return;
    data.pricingPlans.splice(index,1);renderPricing();markDirty();
  }
  if(act==='edit-preview')openPreviewEditor(+btn.dataset.pri);
  if(act==='del-preview'){
    const index=+btn.dataset.pri;if(!confirm('Xóa ảnh Preview này?'))return;
    data.landingPreviewImages.splice(index,1);renderPreviewImages();markDirty();
  }
});
document.addEventListener('change', (e) => {
  const t=e.target;
  if (t.matches('input[type=file][data-act=upload]')) {
    const file=t.files&&t.files[0]; if(!file) return;
    uploadFile(file,'item', +t.dataset.gi, +t.dataset.ii);
  }
});
window.addEventListener('beforeunload', (e) => { if (dirty){ e.preventDefault(); e.returnValue=''; } });

(async function boot(){
  if (!password) { showLogin(); return; }
  try { await tryLogin(password); }
  catch (_) { password=''; sessionStorage.removeItem(STORAGE_KEY); showLogin('Phiên hết hạn hoặc sai mật khẩu.'); }
})();
</script>
</body>
</html>
