(function(){
  const tdict = {
    fr:{dashboard:"Tableau de bord",cars:"Voitures",bookings:"Réservations",settings:"Paramètres",logout:"Déconnexion",welcome:"Bienvenue",total_cars:"Total voitures",total_bookings:"Total réservations",pending:"En attente",revenue:"Revenus",recent_bookings:"Réservations récentes",view_all:"Voir tout",car_management:"Gestion des voitures",manage_fleet:"Gérez votre flotte",add_new_car:"Ajouter une voiture",search_cars:"Rechercher...",all_status:"Tous statuts",all_types:"Tous types",available:"Disponible",reserved:"Réservée",maintenance:"Maintenance",car:"Voiture",type:"Type",year:"Année",price_day:"Prix/Jour",units:"Unités",status:"Statut",actions:"Actions",booking_management:"Gestion des réservations",manage_bookings:"Gérez toutes les réservations",search_bookings:"Rechercher...",customer:"Client",dates:"Dates",days:"Jours",total:"Total",site_settings:"Paramètres du site",site_config:"Configuration générale",change_password:"Changer le mot de passe",admin_profile:"Profil admin",save_settings:"Enregistrer",update_profile:"Mettre à jour",current_password:"Mot de passe actuel",new_password:"Nouveau mot de passe",confirm_password:"Confirmer",full_name:"Nom complet",email:"Email",phone:"Téléphone",site_name:"Nom du site",whatsapp:"WhatsApp",instagram:"URL Instagram",language:"Langue",theme:"Thème",light:"Clair",dark:"Sombre",edit_price:"Modifier prix",price_updated:"Prix mis à jour"},
    ar:{dashboard:"لوحة التحكم",cars:"السيارات",bookings:"الحجوزات",settings:"الإعدادات",logout:"تسجيل الخروج",welcome:"مرحباً",total_cars:"إجمالي السيارات",total_bookings:"إجمالي الحجوزات",pending:"قيد الانتظار",revenue:"الأرباح",recent_bookings:"أحدث الحجوزات",view_all:"عرض الكل",car_management:"إدارة السيارات",manage_fleet:"إدارة الأسطول",add_new_car:"إضافة سيارة",search_cars:"بحث...",all_status:"كل الحالات",all_types:"كل الأنواع",available:"متاحة",reserved:"محجوزة",maintenance:"صيانة",car:"السيارة",type:"النوع",year:"السنة",price_day:"السعر/يوم",units:"الوحدات",status:"الحالة",actions:"إجراءات",booking_management:"إدارة الحجوزات",manage_bookings:"إدارة جميع الحجوزات",search_bookings:"بحث...",customer:"العميل",dates:"التواريخ",days:"الأيام",total:"المجموع",site_settings:"إعدادات الموقع",site_config:"الإعدادات العامة",change_password:"تغيير كلمة المرور",admin_profile:"الملف الشخصي",save_settings:"حفظ الإعدادات",update_profile:"تحديث الملف",current_password:"كلمة المرور الحالية",new_password:"كلمة المرور الجديدة",confirm_password:"تأكيد كلمة المرور",full_name:"الاسم الكامل",email:"البريد الإلكتروني",phone:"الهاتف",site_name:"اسم الموقع",whatsapp:"واتساب",instagram:"رابط إنستغرام",language:"اللغة",theme:"المظهر",light:"فاتح",dark:"داكن",edit_price:"تعديل السعر",price_updated:"تم تحديث السعر"},
    en:{dashboard:"Dashboard",cars:"Cars",bookings:"Bookings",settings:"Settings",logout:"Logout",welcome:"Welcome back",total_cars:"Total Cars",total_bookings:"Total Bookings",pending:"Pending",revenue:"Revenue",recent_bookings:"Recent Bookings",view_all:"View All",car_management:"Car Management",manage_fleet:"Manage your fleet",add_new_car:"Add New Car",search_cars:"Search cars...",all_status:"All Status",all_types:"All Types",available:"Available",reserved:"Reserved",maintenance:"Maintenance",car:"Car",type:"Type",year:"Year",price_day:"Price/Day",units:"Units",status:"Status",actions:"Actions",booking_management:"Booking Management",manage_bookings:"Manage all rental bookings",search_bookings:"Search bookings...",customer:"Customer",dates:"Dates",days:"Days",total:"Total",site_settings:"Site Settings",site_config:"General site configuration",change_password:"Change Password",admin_profile:"Admin Profile",save_settings:"Save Settings",update_profile:"Update Profile",current_password:"Current Password",new_password:"New Password",confirm_password:"Confirm Password",full_name:"Full Name",email:"Email",phone:"Phone",site_name:"Site Name",whatsapp:"WhatsApp",instagram:"Instagram URL",language:"Language",theme:"Theme",light:"Light",dark:"Dark",edit_price:"Edit price",price_updated:"Price updated"}
  };
  function getLang(){ try{return localStorage.getItem('site_lang')||'fr'}catch(e){return 'fr'} }
  function setLang(l){ try{localStorage.setItem('site_lang',l)}catch(e){} apply(); }
  function getTheme(){ try{return localStorage.getItem('site_theme')||'dark'}catch(e){return 'dark'} }
  function setTheme(th){ try{localStorage.setItem('site_theme',th)}catch(e){} apply(); }
  function t(k){ const l=getLang(); return (tdict[l]&&tdict[l][k])||tdict.en[k]||k; }
  function ensureControls(){
    const header = document.querySelector('header .flex.items-center.justify-between') || document.querySelector('header > div');
    if(!header || document.getElementById('adminLangThemeControls')) return;
    const wrap = document.createElement('div');
    wrap.id='adminLangThemeControls';
    wrap.className='flex items-center gap-2';
    wrap.innerHTML = `
      <select id="adminLangSelect" class="lang-select" style="padding:.35rem .6rem;border-radius:.6rem;background:rgba(30,41,59,.6);color:#e2e8f0;border:1px solid rgba(255,255,255,.1)">
        <option value="fr">FR</option><option value="ar">AR</option><option value="en">EN</option>
      </select>
      <button id="adminThemeToggle" class="theme-toggle-btn" style="width:36px;height:36px;border-radius:.6rem;background:rgba(30,41,59,.6);color:#e2e8f0;border:1px solid rgba(255,255,255,.1)" title="Theme"><i class="fas fa-moon"></i></button>`;
    const right = header.querySelector('.flex.items-center.gap-4:last-child') || header.lastElementChild;
    if(right) right.insertBefore(wrap, right.firstChild); else header.appendChild(wrap);
    const sel=document.getElementById('adminLangSelect'); const btn=document.getElementById('adminThemeToggle');
    if(sel){ sel.value=getLang(); sel.addEventListener('change',e=>setLang(e.target.value)); }
    if(btn){ btn.addEventListener('click',()=>setTheme(getTheme()==='dark'?'light':'dark')); }
  }
  function apply(){
    const l=getLang(), th=getTheme();
    document.documentElement.lang=l; document.documentElement.dir = l==='ar'?'rtl':'ltr';
    document.documentElement.classList.toggle('light', th==='light');
    document.body.style.backgroundColor = th==='light' ? '#f1f5f9' : '#0a1628';
    document.body.style.color = th==='light' ? '#0f172a' : '#ffffff';
    document.querySelectorAll('[data-i18n]').forEach(el=>{ const k=el.getAttribute('data-i18n'); if(k) el.textContent=t(k); });
    document.querySelectorAll('[data-i18n-placeholder]').forEach(el=>{ const k=el.getAttribute('data-i18n-placeholder'); if(k) el.setAttribute('placeholder', t(k)); });
    const sel=document.getElementById('adminLangSelect'); if(sel) sel.value=l;
    const btn=document.getElementById('adminThemeToggle i'); if(btn) btn.className = 'fas ' + (th==='dark'?'fa-moon':'fa-sun');
  }
  window.__adminI18n = {t, setLang, setTheme, getLang, getTheme, apply};
  document.addEventListener('DOMContentLoaded', function(){ ensureControls(); apply(); });
})();
