<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ibrahim Azzouz Cars - Premium Car Rental in Tetouan, Morocco</title>
    <script>
        (function() {
            try {
                var theme = localStorage.getItem('site_theme') || 'dark';
                var lang = localStorage.getItem('site_lang') || 'fr';
                document.documentElement.classList.toggle('light', theme === 'light');
                document.documentElement.setAttribute('data-theme', theme);
                document.documentElement.lang = lang;
                document.documentElement.dir = (lang === 'ar') ? 'rtl' : 'ltr';
            } catch (e) {}
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/site-enhancements.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a8a',
                        accent: '#06b6d4',
                        gold: '#f59e0b',
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Cairo', sans-serif; background: #0f172a; color: #f1f5f9; transition: background .25s ease, color .25s ease; }
        .glass { background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px); }
        .gradient-text { background: linear-gradient(135deg, #f59e0b, #fbbf24); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .card-hover:hover { transform: translateY(-10px); box-shadow: 0 30px 60px rgba(6, 182, 212, 0.15); }
        .hero-bg {
            background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.8)),
                        url('https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=1920&q=80') center/cover no-repeat;
        }
        .nav-link { position: relative; }
        .nav-link::after { content: ''; position: absolute; bottom: -4px; left: 0; width: 0; height: 2px; background: linear-gradient(135deg, #f59e0b, #fbbf24); transition: width 0.3s; }
        .nav-link:hover::after { width: 100%; }
        .booking-modal { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(30px); }
        .booking-modal input, .booking-modal select, .booking-modal textarea {
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(71, 85, 105, 0.5);
            transition: all 0.3s;
        }
        .booking-modal input:focus, .booking-modal select:focus, .booking-modal textarea:focus {
            outline: none;
            border-color: #06b6d4;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.2);
        }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.5s ease forwards; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
        .pulse-btn:hover { animation: pulse 1s infinite; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #1e3a8a; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #06b6d4; }
    </style>
</head>
<body>
    <div id="root"></div>

    <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/framer-motion/10.16.4/framer-motion.js"></script>
    <script type="text/babel">
        const { useState, useEffect, useRef, useCallback, createContext, useContext } = React;
        const { motion, AnimatePresence } = window['framer-motion'] || { motion: { div: 'div', button: 'button', a: 'a', section: 'section', img: 'img' }, AnimatePresence: ({children}) => children };

        const today = new Date().toISOString().split('T')[0];

        const translations = {
          fr: {
            nav_home: "Accueil", nav_about: "À propos", nav_fleet: "Flotte", nav_services: "Services", nav_contact: "Contact",
            whatsapp: "WhatsApp", premium_rental: "Location de voitures premium",
            hero_badge: "Location de voitures premium à Tétouan",
            hero_title_1: "Votre partenaire", hero_title_2: "de location de confiance", hero_title_3: "à Tétouan, Maroc",
            hero_sub: "Découvrez la liberté d'explorer le Maroc avec notre flotte premium. Des trajets en ville aux aventures en montagne, nous sommes là pour vous.",
            explore_fleet: "Découvrir notre flotte", contact_us: "Nous contacter", find_us: "Nous trouver",
            stat_clients: "Clients satisfaits", stat_vehicles: "Véhicules", stat_years: "Années d'expérience",
            about_badge: "À propos", about_title_1: "Le partenaire", about_title_2: "de confiance", about_title_3: "à Tétouan",
            about_p1: "Ibrahim Azzouz Cars est une entreprise de location de voitures leader à Tétouan. Avec plus d'une décennie d'expérience, nous avons bâti notre réputation sur la fiabilité et un service client exceptionnel.",
            about_p2: "Que vous ayez besoin d'une citadine, d'un SUV ou d'un véhicule premium, notre flotte diversifiée a le véhicule idéal pour vous.",
            about_feat1_t: "Service professionnel", about_feat1_d: "Un service personnalisé adapté à vos besoins.",
            about_feat2_t: "Meilleurs prix", about_feat2_d: "Des tarifs compétitifs sans compromis sur la qualité.",
            about_feat3_t: "Flotte de qualité", about_feat3_d: "Des véhicules modernes et bien entretenus.",
            about_feat4_t: "Client d'abord", about_feat4_d: "Votre satisfaction est notre priorité.",
            showcase_badge: "Expérience Premium", showcase_title: "Élégance & Performance", showcase_desc: "Découvrez une sélection haut de gamme pensée pour le confort, la puissance et le prestige. Notre flotte inclut des véhicules d'exception pour vos déplacements professionnels et vos moments spéciaux.",
            showcase_cta: "Réserver maintenant",
            fleet_badge: "Notre flotte", fleet_title_1: "Choisissez votre", fleet_title_2: "véhicule idéal",
            fleet_sub: "Des voitures économiques aux berlines luxueuses, nous avons le véhicule parfait pour chaque occasion.",
            all_cars: "Toutes les voitures", loading_fleet: "Chargement de la flotte...", book_now: "Réserver", mad_day: "MAD/jour",
            seats: "places",
            services_badge: "Nos services", services_title_1: "Ce que nous", services_title_2: "offrons",
            services_sub: "Des solutions complètes de location adaptées à tous vos besoins.",
            svc1_t: "Location courte durée", svc1_d: "Louez pour un jour ou une semaine avec flexibilité.",
            svc2_t: "Location longue durée", svc2_d: "Économisez avec nos forfaits mensuels et annuels.",
            svc3_t: "Transfert aéroport", svc3_d: "Un service fiable pour commencer votre voyage sereinement.",
            svc4_t: "Support 24/7", svc4_d: "Notre équipe est disponible à tout moment pour vous aider.",
            why_badge: "Pourquoi nous choisir", why_title_1: "La différence", why_title_2: "Ibrahim Azzouz",
            why_sub: "Nous offrons bien plus qu'une location : tranquillité d'esprit et expérience premium.",
            why1_t: "Service fiable", why1_d: "Plus d'une décennie de confiance et de clients satisfaits.",
            why2_t: "Prix compétitifs", why2_d: "Tarifs transparents, sans frais cachés.",
            why3_t: "Véhicules entretenus", why3_d: "Chaque véhicule est inspecté et entretenu régulièrement.",
            why4_t: "Réservation rapide", why4_d: "Réservez en quelques minutes, simplement.",
            test_badge: "Témoignages", test_title_1: "Ce que disent", test_title_2: "nos clients",
            test_sub: "Découvrez les avis de nos clients satisfaits.",
            contact_badge: "Contact", contact_title_1: "Contactez", contact_title_2: "nous",
            contact_sub: "Prêt à réserver ? Une question ? Nous sommes là pour vous aider !",
            phone: "Téléphone", email: "Email", address: "Adresse", city_country: "Tétouan, Maroc", call_anytime: "Appelez-nous à tout moment",
            chat_direct: "Discutez directement avec nous", follow_ig: "Suivez-nous sur Instagram", send_email: "Envoyez-nous un email", visit_office: "Visitez notre agence",
            footer_desc: "Le service de location de voitures le plus fiable de Tétouan. Véhicules de qualité, prix compétitifs et service exceptionnel depuis 2014.",
            quick_links: "Liens rapides", services_footer: "Services", contact_info: "Informations de contact",
            rights: "Tous droits réservés.", made_with: "Fait avec", in_tetouan: "à Tétouan, Maroc",
            booking_title: "Réservation", full_name: "Nom complet *", customer_email: "Email *", customer_phone: "Téléphone *", customer_city: "Ville",
            start_date: "Date de début *", end_date: "Date de fin *", notes: "Notes", daily_rate: "Tarif journalier", number_of_days: "Nombre de jours", total: "Total",
            confirm_booking: "Confirmer la réservation", processing: "Traitement...", booking_submitted: "Réservation envoyée !",
            booking_received: "Votre réservation", for_car: "pour", has_been_received: "a été reçue.", we_contact: "Nous vous contacterons bientôt pour confirmer.",
            close: "Fermer", booking_failed: "Échec de l'envoi. Veuillez réessayer.", network_error: "Erreur réseau. Vérifiez votre connexion.",
            theme_dark: "Mode sombre", theme_light: "Mode clair", language: "Langue"
          },
          ar: {
            nav_home: "الرئيسية", nav_about: "من نحن", nav_fleet: "الأسطول", nav_services: "الخدمات", nav_contact: "اتصل بنا",
            whatsapp: "واتساب", premium_rental: "تأجير سيارات فاخر",
            hero_badge: "تأجير سيارات فاخر في تطوان",
            hero_title_1: "شريككم", hero_title_2: "الموثوق لتأجير السيارات", hero_title_3: "في تطوان، المغرب",
            hero_sub: "اكتشف حرية استكشاف المغرب مع أسطولنا المميز. من التنقل داخل المدينة إلى مغامرات الجبال، نحن معك.",
            explore_fleet: "استكشف الأسطول", contact_us: "اتصل بنا", find_us: "موقعنا",
            stat_clients: "عملاء سعداء", stat_vehicles: "مركبات", stat_years: "سنوات خبرة",
            about_badge: "من نحن", about_title_1: "الشريك", about_title_2: "الموثوق", about_title_3: "في تطوان",
            about_p1: "إبراهيم عزوز كارز شركة رائدة في تأجير السيارات بمدينة تطوان. بخبرة تزيد عن عشر سنوات، بنينا سمعتنا على الموثوقية وخدمة العملاء المميزة.",
            about_p2: "سواء كنت تحتاج سيارة اقتصادية أو SUV أو مركبة فاخرة، لدينا الخيار المناسب لك.",
            about_feat1_t: "خدمة احترافية", about_feat1_d: "خدمة مخصصة تلبي احتياجاتك.",
            about_feat2_t: "أفضل الأسعار", about_feat2_d: "أسعار تنافسية مع جودة عالية.",
            about_feat3_t: "أسطول عالي الجودة", about_feat3_d: "مركبات حديثة وصيانة منتظمة.",
            about_feat4_t: "العميل أولاً", about_feat4_d: "رضاكم هو أولويتنا.",
            showcase_badge: "تجربة فاخرة", showcase_title: "أناقة وأداء", showcase_desc: "اكتشف تشكيلة فاخرة تجمع بين الراحة والقوة والفخامة. يضم أسطولنا سيارات استثنائية لتنقلاتك المهنية ومناسباتك الخاصة.",
            showcase_cta: "احجز الآن",
            fleet_badge: "أسطولنا", fleet_title_1: "اختر", fleet_title_2: "سيارتك المثالية",
            fleet_sub: "من السيارات الاقتصادية إلى السيدان الفاخرة، لدينا السيارة المناسبة لكل مناسبة.",
            all_cars: "كل السيارات", loading_fleet: "جارٍ تحميل الأسطول...", book_now: "احجز الآن", mad_day: "درهم/يوم",
            seats: "مقاعد",
            services_badge: "خدماتنا", services_title_1: "ماذا", services_title_2: "نقدم",
            services_sub: "حلول تأجير متكاملة لتلبية جميع احتياجاتك.",
            svc1_t: "تأجير قصير المدى", svc1_d: "استأجر ليوم أو أسبوع بمرونة كاملة.",
            svc2_t: "تأجير طويل المدى", svc2_d: "وفّر مع باقاتنا الشهرية والسنوية.",
            svc3_t: "نقل المطار", svc3_d: "خدمة موثوقة لبدء رحلتك براحة.",
            svc4_t: "دعم 24/7", svc4_d: "فريقنا متاح في أي وقت لمساعدتك.",
            why_badge: "لماذا تختارنا", why_title_1: "الفرق مع", why_title_2: "إبراهيم عزوز",
            why_sub: "نحن لا نؤجر السيارات فقط، بل نقدم راحة بال وتجربة مميزة.",
            why1_t: "خدمة موثوقة", why1_d: "أكثر من عشر سنوات من الثقة والعملاء الراضين.",
            why2_t: "أسعار تنافسية", why2_d: "أسعار شفافة بدون رسوم خفية.",
            why3_t: "صيانة ممتازة", why3_d: "يتم فحص وصيانة كل مركبة بانتظام.",
            why4_t: "حجز سريع", why4_d: "احجز سيارتك في دقائق بسهولة.",
            test_badge: "آراء العملاء", test_title_1: "ماذا يقول", test_title_2: "عملاؤنا",
            test_sub: "تعرف على آراء عملائنا الراضين.",
            contact_badge: "تواصل معنا", contact_title_1: "اتصل", contact_title_2: "بنا",
            contact_sub: "جاهز للحجز؟ لديك سؤال؟ نحن هنا لمساعدتك!",
            phone: "الهاتف", email: "البريد الإلكتروني", address: "العنوان", city_country: "تطوان، المغرب", call_anytime: "اتصل بنا في أي وقت",
            chat_direct: "تحدث معنا مباشرة", follow_ig: "تابعنا على إنستغرام", send_email: "أرسل لنا بريداً", visit_office: "زر مكتبنا",
            footer_desc: "أكثر خدمات تأجير السيارات موثوقية في تطوان. مركبات عالية الجودة وأسعار تنافسية وخدمة مميزة منذ 2014.",
            quick_links: "روابط سريعة", services_footer: "الخدمات", contact_info: "معلومات التواصل",
            rights: "جميع الحقوق محفوظة.", made_with: "صنع بـ", in_tetouan: "في تطوان، المغرب",
            booking_title: "الحجز", full_name: "الاسم الكامل *", customer_email: "البريد الإلكتروني *", customer_phone: "الهاتف *", customer_city: "المدينة",
            start_date: "تاريخ البداية *", end_date: "تاريخ النهاية *", notes: "ملاحظات", daily_rate: "السعر اليومي", number_of_days: "عدد الأيام", total: "المجموع",
            confirm_booking: "تأكيد الحجز", processing: "جارٍ المعالجة...", booking_submitted: "تم إرسال الحجز!",
            booking_received: "تم استلام حجزك", for_car: "لـ", has_been_received: ".", we_contact: "سنتواصل معك قريباً لتأكيد الحجز.",
            close: "إغلاق", booking_failed: "فشل الإرسال. حاول مرة أخرى.", network_error: "خطأ في الشبكة. تحقق من الاتصال.",
            theme_dark: "الوضع الداكن", theme_light: "الوضع الفاتح", language: "اللغة"
          },
          en: {
            nav_home: "Home", nav_about: "About", nav_fleet: "Fleet", nav_services: "Services", nav_contact: "Contact",
            whatsapp: "WhatsApp", premium_rental: "Premium Car Rental",
            hero_badge: "Premium Car Rental in Tetouan",
            hero_title_1: "Your Trusted", hero_title_2: "Car Rental Partner", hero_title_3: "in Tetouan, Morocco",
            hero_sub: "Experience the freedom of exploring Morocco with our premium fleet of vehicles. From city drives to mountain adventures, we've got you covered.",
            explore_fleet: "Explore Our Fleet", contact_us: "Contact Us", find_us: "Find Us",
            stat_clients: "Happy Clients", stat_vehicles: "Vehicles", stat_years: "Years Experience",
            about_badge: "About Us", about_title_1: "Tetouan's", about_title_2: "Trusted", about_title_3: "Car Rental Partner",
            about_p1: "Ibrahim Azzouz Cars is a leading car rental company based in the beautiful city of Tetouan, Morocco. With over a decade of experience, we've built our reputation on reliability, affordability, and exceptional customer service.",
            about_p2: "Whether you need a compact car for city exploration, an SUV for mountain adventures, or a luxury vehicle for special occasions, our diverse fleet has the perfect vehicle waiting for you.",
            about_feat1_t: "Professional Service", about_feat1_d: "Personalized service tailored to your needs.",
            about_feat2_t: "Best Prices", about_feat2_d: "Competitive rates without compromising on quality.",
            about_feat3_t: "Quality Fleet", about_feat3_d: "Regularly maintained, modern vehicles.",
            about_feat4_t: "Customer First", about_feat4_d: "Your satisfaction is our priority.",
            showcase_badge: "Premium Experience", showcase_title: "Elegance & Performance", showcase_desc: "Discover a high-end selection designed for comfort, power, and prestige. Our fleet includes exceptional vehicles for business trips and special moments.",
            showcase_cta: "Book Now",
            fleet_badge: "Our Fleet", fleet_title_1: "Choose Your", fleet_title_2: "Perfect Ride",
            fleet_sub: "From economical city cars to luxurious sedans, we have the perfect vehicle for every occasion.",
            all_cars: "All Cars", loading_fleet: "Loading our fleet...", book_now: "Book Now", mad_day: "MAD/day",
            seats: "seats",
            services_badge: "Our Services", services_title_1: "What We", services_title_2: "Offer",
            services_sub: "Comprehensive car rental solutions designed to meet your every need.",
            svc1_t: "Short-Term Rental", svc1_d: "Rent for a day or a week with full flexibility.",
            svc2_t: "Long-Term Rental", svc2_d: "Save more with our monthly and yearly packages.",
            svc3_t: "Airport Transfer", svc3_d: "Reliable transfers to start your journey stress-free.",
            svc4_t: "24/7 Support", svc4_d: "Our team is available around the clock to help.",
            why_badge: "Why Choose Us", why_title_1: "The", why_title_2: "Ibrahim Azzouz Difference",
            why_sub: "We don't just rent cars. We provide peace of mind and exceptional experiences.",
            why1_t: "Trusted Service", why1_d: "Over a decade of reliable service and satisfied customers.",
            why2_t: "Competitive Pricing", why2_d: "Transparent pricing with no hidden fees.",
            why3_t: "Well-Maintained", why3_d: "Every vehicle is inspected and maintained regularly.",
            why4_t: "Instant Booking", why4_d: "Book in minutes with our streamlined process.",
            test_badge: "Testimonials", test_title_1: "What Our", test_title_2: "Clients Say",
            test_sub: "Hear from our satisfied customers.",
            contact_badge: "Get In Touch", contact_title_1: "Contact", contact_title_2: "Us",
            contact_sub: "Ready to book? Have questions? We're here to help!",
            phone: "Phone", email: "Email", address: "Address", city_country: "Tetouan, Morocco", call_anytime: "Call us anytime",
            chat_direct: "Chat with us directly", follow_ig: "Follow us on Instagram", send_email: "Send us an email", visit_office: "Visit our office",
            footer_desc: "Tetouan's most trusted car rental service. Quality vehicles, competitive prices, and exceptional customer service since 2014.",
            quick_links: "Quick Links", services_footer: "Services", contact_info: "Contact Info",
            rights: "All rights reserved.", made_with: "Made with", in_tetouan: "in Tetouan, Morocco",
            booking_title: "Booking", full_name: "Full Name *", customer_email: "Email *", customer_phone: "Phone *", customer_city: "City",
            start_date: "Start Date *", end_date: "End Date *", notes: "Notes", daily_rate: "Daily rate", number_of_days: "Number of days", total: "Total",
            confirm_booking: "Confirm Booking", processing: "Processing...", booking_submitted: "Booking Submitted!",
            booking_received: "Your booking", for_car: "for", has_been_received: "has been received.", we_contact: "We will contact you shortly to confirm your reservation.",
            close: "Close", booking_failed: "Failed to submit booking. Please try again.", network_error: "Network error. Please check your connection and try again.",
            theme_dark: "Dark mode", theme_light: "Light mode", language: "Language"
          }
        };

        const LangThemeContext = createContext(null);
        const useSite = () => useContext(LangThemeContext);

        function SiteProvider({ children }) {
            const [lang, setLangState] = useState(localStorage.getItem('site_lang') || 'fr');
            const [theme, setThemeState] = useState(localStorage.getItem('site_theme') || 'dark');

            useEffect(() => {
                localStorage.setItem('site_lang', lang);
                document.documentElement.lang = lang;
                document.documentElement.dir = lang === 'ar' ? 'rtl' : 'ltr';
            }, [lang]);

            useEffect(() => {
                localStorage.setItem('site_theme', theme);
                document.documentElement.classList.toggle('light', theme === 'light');
                document.documentElement.setAttribute('data-theme', theme);
            }, [theme]);

            const t = (key) => (translations[lang] && translations[lang][key]) || translations.en[key] || key;
            const setLang = (l) => setLangState(l);
            const toggleTheme = () => setThemeState(prev => prev === 'dark' ? 'light' : 'dark');

            return (
                <LangThemeContext.Provider value={{ lang, setLang, theme, toggleTheme, t }}>
                    {children}
                </LangThemeContext.Provider>
            );
        }

        function Navbar() {
            const { lang, setLang, theme, toggleTheme, t } = useSite();
            const [scrolled, setScrolled] = useState(false);
            const [mobileOpen, setMobileOpen] = useState(false);

            useEffect(() => {
                const handler = () => setScrolled(window.scrollY > 50);
                window.addEventListener('scroll', handler);
                return () => window.removeEventListener('scroll', handler);
            }, []);

            const links = [
                { name: t('nav_home'), href: '#home' },
                { name: t('nav_about'), href: '#about' },
                { name: t('nav_fleet'), href: '#fleet' },
                { name: t('nav_services'), href: '#services' },
                { name: t('nav_contact'), href: '#contact' },
            ];

            return (
                <nav className={`fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${scrolled ? 'glass border-b border-slate-700/50 shadow-xl' : ''}`}>
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex items-center justify-between h-20">
                            <a href="#home" className="flex items-center gap-3">
                                <div className="w-12 h-12 bg-gradient-to-br from-gold to-amber-400 rounded-xl flex items-center justify-center shadow-lg">
                                    <i className="fas fa-car text-primary text-xl font-bold"></i>
                                </div>
                                <div>
                                    <span className="text-xl font-bold gradient-text">Ibrahim Azzouz</span>
                                    <span className="block text-xs text-slate-400 -mt-1">{t('premium_rental')}</span>
                                </div>
                            </a>
                            <div className="hidden md:flex items-center gap-4 lg:gap-6">
                                {links.map(link => (
                                    <a key={link.href} href={link.href} className="nav-link text-slate-300 hover:text-gold transition-colors font-medium">{link.name}</a>
                                ))}
                                <select value={lang} onChange={(e)=>setLang(e.target.value)} className="lang-select" aria-label={t('language')} title={t('language')}>
                                    <option value="fr">FR</option>
                                    <option value="ar">AR</option>
                                    <option value="en">EN</option>
                                </select>
                                <button onClick={toggleTheme} className="theme-toggle-btn" title={theme === 'dark' ? t('theme_light') : t('theme_dark')} aria-label="Toggle theme">
                                    <i className={`fas ${theme === 'dark' ? 'fa-moon' : 'fa-sun'}`}></i>
                                </button>
                                <a href="https://wa.me/212764730187?text=Hello!%20I%27m%20interested%20in%20renting%20a%20car" target="_blank" rel="noopener noreferrer"
                                   className="bg-gradient-to-r from-green-500 to-green-600 text-white px-5 py-2.5 rounded-full font-semibold hover:shadow-lg hover:shadow-green-500/30 transition-all flex items-center gap-2 pulse-btn">
                                    <i className="fab fa-whatsapp text-lg"></i> {t('whatsapp')}
                                </a>
                            </div>
                            <div className="md:hidden flex items-center gap-2">
                                <button onClick={toggleTheme} className="theme-toggle-btn" aria-label="Toggle theme"><i className={`fas ${theme === 'dark' ? 'fa-moon' : 'fa-sun'}`}></i></button>
                                <button onClick={() => setMobileOpen(!mobileOpen)} className="text-slate-300 hover:text-gold text-2xl px-2">
                                    <i className={mobileOpen ? 'fas fa-times' : 'fas fa-bars'}></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    {mobileOpen && (
                        <motion.div initial={{ opacity: 0, height: 0 }} animate={{ opacity: 1, height: 'auto' }} exit={{ opacity: 0, height: 0 }}
                                     className="md:hidden glass border-t border-slate-700/50">
                            <div className="px-4 py-4 space-y-3">
                                {links.map(link => (
                                    <a key={link.href} href={link.href} onClick={() => setMobileOpen(false)}
                                       className="block text-slate-300 hover:text-gold transition-colors font-medium py-2">{link.name}</a>
                                ))}
                                <div className="flex items-center gap-3 pt-2">
                                    <select value={lang} onChange={(e)=>setLang(e.target.value)} className="lang-select flex-1">
                                        <option value="fr">Français 🇫🇷</option>
                                        <option value="ar">العربية 🇲🇦</option>
                                        <option value="en">English 🇬🇧</option>
                                    </select>
                                </div>
                                <a href="https://wa.me/212764730187" target="_blank" rel="noopener noreferrer"
                                   className="block bg-gradient-to-r from-green-500 to-green-600 text-white px-5 py-2.5 rounded-full font-semibold text-center mt-3">
                                    <i className="fab fa-whatsapp mr-2"></i> {t('whatsapp')}
                                </a>
                            </div>
                        </motion.div>
                    )}
                </nav>
            );
        }

        function Hero() {
            const { t } = useSite();
            return (
                <section id="home" className="hero-bg min-h-screen flex items-center relative">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32 w-full">
                        <motion.div initial={{ opacity: 0, y: 40 }} animate={{ opacity: 1, y: 0 }} transition={{ duration: 0.8 }}>
                            <div className="inline-flex items-center gap-2 bg-gold/10 border border-gold/30 rounded-full px-4 py-2 mb-6">
                                <i className="fas fa-star text-gold text-sm"></i>
                                <span className="text-gold text-sm font-medium">{t('hero_badge')}</span>
                            </div>
                            <h1 className="text-4xl md:text-6xl lg:text-7xl font-black mb-6 leading-tight">
                                {t('hero_title_1')}<br />
                                <span className="gradient-text">{t('hero_title_2')}</span><br />
                                <span className="text-3xl md:text-4xl lg:text-5xl text-slate-300">{t('hero_title_3')}</span>
                            </h1>
                            <p className="text-lg md:text-xl text-slate-400 max-w-2xl mb-10 leading-relaxed">
                                {t('hero_sub')}
                            </p>
                            <div className="flex flex-col sm:flex-row gap-4 mb-16">
                                <a href="#fleet" className="bg-gradient-to-r from-gold to-amber-400 text-primary px-8 py-4 rounded-full font-bold text-lg hover:shadow-xl hover:shadow-gold/30 transition-all text-center">
                                    <i className="fas fa-car mr-2"></i> {t('explore_fleet')}
                                </a>
                                <a href="#contact" className="border-2 border-slate-600 text-slate-300 px-8 py-4 rounded-full font-bold text-lg hover:border-accent hover:text-accent transition-all text-center">
                                    <i className="fas fa-phone mr-2"></i> {t('contact_us')}
                                </a>
                                <a href="https://maps.app.goo.gl/kzU2eGq3kqTwzUPv7" target="_blank" rel="noopener noreferrer" className="border-2 border-slate-600 text-slate-300 px-8 py-4 rounded-full font-bold text-lg hover:border-blue-400 hover:text-blue-400 transition-all text-center">
                                    <i className="fas fa-map-marker-alt mr-2"></i> {t('find_us')}
                                </a>
                            </div>
                            <div className="grid grid-cols-3 gap-8 max-w-lg">
                                <div className="text-center">
                                    <div className="text-3xl md:text-4xl font-black gradient-text">500+</div>
                                    <div className="text-slate-400 text-sm mt-1">{t('stat_clients')}</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-3xl md:text-4xl font-black gradient-text">50+</div>
                                    <div className="text-slate-400 text-sm mt-1">{t('stat_vehicles')}</div>
                                </div>
                                <div className="text-center">
                                    <div className="text-3xl md:text-4xl font-black gradient-text">10+</div>
                                    <div className="text-slate-400 text-sm mt-1">{t('stat_years')}</div>
                                </div>
                            </div>
                        </motion.div>
                    </div>
                    <div className="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
                        <a href="#about" className="text-slate-400 hover:text-gold transition-colors">
                            <i className="fas fa-chevron-down text-2xl"></i>
                        </a>
                    </div>
                </section>
            );
        }

        function About() {
            const { t } = useSite();
            return (
                <section id="about" className="py-24 bg-gradient-to-b from-[#0f172a] to-[#1e293b]">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                            <motion.div initial={{ opacity: 0, y: 24 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ duration: 0.6 }} className="order-2 lg:order-1">
                                <div className="inline-flex items-center gap-2 bg-gold/10 border border-gold/30 rounded-full px-4 py-2 mb-5">
                                    <i className="fas fa-info-circle text-gold text-sm"></i>
                                    <span className="text-gold text-sm font-medium">{t('about_badge')}</span>
                                </div>
                                <h2 className="text-3xl md:text-5xl font-black mb-6 leading-tight">
                                    {t('about_title_1')} <span className="gradient-text">{t('about_title_2')}</span> {t('about_title_3')}
                                </h2>
                                <div className="space-y-4 mb-8">
                                    <p className="text-slate-400 text-base md:text-lg leading-relaxed">{t('about_p1')}</p>
                                    <p className="text-slate-400 text-base md:text-lg leading-relaxed">{t('about_p2')}</p>
                                </div>
                                <div className="grid sm:grid-cols-2 gap-4">
                                    {[
                                        { icon: 'fa-shield-alt', title: t('about_feat1_t'), desc: t('about_feat1_d') },
                                        { icon: 'fa-tags', title: t('about_feat2_t'), desc: t('about_feat2_d') },
                                        { icon: 'fa-car', title: t('about_feat3_t'), desc: t('about_feat3_d') },
                                        { icon: 'fa-heart', title: t('about_feat4_t'), desc: t('about_feat4_d') },
                                    ].map((f, i) => (
                                        <div key={i} className="flex items-start gap-3 p-4 rounded-xl bg-slate-800/50 border border-slate-700/50 card-hover transition-all duration-300">
                                            <div className="w-10 h-10 bg-gradient-to-br from-gold to-amber-400 rounded-lg flex items-center justify-center flex-shrink-0 shadow-lg shadow-gold/10">
                                                <i className={`fas ${f.icon} text-primary text-sm`}></i>
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-sm mb-1 text-white">{f.title}</h4>
                                                <p className="text-slate-400 text-xs leading-relaxed">{f.desc}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </motion.div>

                            <motion.div initial={{ opacity: 0, y: 24 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ duration: 0.6, delay: 0.1 }} className="relative order-1 lg:order-2">
                                <div className="absolute -inset-4 bg-gradient-to-tr from-gold/15 via-transparent to-accent/10 rounded-[2rem] blur-2xl"></div>
                                <div className="group relative rounded-[2rem] overflow-hidden border border-slate-700/60 shadow-2xl shadow-black/30">
                                    <img
                                        src="https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1400&q=80"
                                        alt={t('about_badge')}
                                        loading="lazy"
                                        decoding="async"
                                        className="w-full h-[360px] sm:h-[440px] lg:h-[560px] object-cover transform transition-transform duration-700 ease-out group-hover:scale-105"
                                        onError={(e)=>{ e.target.onerror=null; e.target.src='https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1400&q=80'; }}
                                    />
                                    <div className="absolute inset-0 bg-gradient-to-t from-[#0b1220]/85 via-[#0b1220]/20 to-transparent"></div>
                                    <div className="absolute inset-0 bg-gradient-to-r from-[#0b1220]/20 via-transparent to-transparent"></div>
                                    <div className="absolute top-5 left-5 sm:top-6 sm:left-6 inline-flex items-center gap-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-full px-3.5 py-1.5">
                                        <span className="w-2 h-2 rounded-full bg-gold"></span>
                                        <span className="text-white text-xs sm:text-sm font-medium">{t('showcase_badge')}</span>
                                    </div>
                                    <div className="absolute bottom-0 left-0 right-0 p-5 sm:p-7">
                                        <div className="max-w-md bg-slate-900/35 backdrop-blur-md border border-white/10 rounded-2xl p-4 sm:p-5">
                                            <p className="text-gold text-xs uppercase tracking-[0.18em] font-semibold mb-2">{t('showcase_title')}</p>
                                            <p className="text-white text-lg sm:text-xl font-bold leading-snug mb-2">Premium Fleet & Driving Experience</p>
                                            <p className="text-slate-200/90 text-sm leading-relaxed">{t('showcase_desc')}</p>
                                        </div>
                                    </div>
                                </div>
                            </motion.div>
                        </div>
                    </div>
                </section>
            );
        }

        function BookingModal({ car, isOpen, onClose }) {
            const { t } = useSite();
            const [form, setForm] = useState({ customer_name: '', customer_email: '', customer_phone: '', customer_city: '', start_date: today, end_date: today, notes: '' });
            const [loading, setLoading] = useState(false);
            const [success, setSuccess] = useState(null);
            const [error, setError] = useState('');
            const calcDays = () => { const s = new Date(form.start_date); const e = new Date(form.end_date); const diff = Math.ceil((e - s) / (1000 * 60 * 60 * 24)); return diff > 0 ? diff : 1; };
            const totalPrice = car ? (calcDays() * parseFloat(car.price_per_day || car.daily_price || car.price || 0)).toFixed(2) : '0.00';
            const handleChange = (e) => { const { name, value } = e.target; setForm(prev => { const updated = { ...prev, [name]: value }; if (name === 'start_date' && updated.end_date < updated.start_date) updated.end_date = updated.start_date; return updated; }); };
            const handleSubmit = async (e) => {
                e.preventDefault(); setLoading(true); setError('');
                try {
                    const payload = { ...form, car_id: car.id, total_price: totalPrice };
                    const res = await fetch('api/bookings.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
                    const data = await res.json();
                    if (data.success) setSuccess(data); else setError(data.message || t('booking_failed'));
                } catch (err) { setError(t('network_error')); }
                setLoading(false);
            };
            if (!isOpen || !car) return null;
            return (
                <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }} exit={{ opacity: 0 }} className="fixed inset-0 z-[100] flex items-center justify-center p-4" onClick={onClose}>
                    <div className="absolute inset-0 bg-black/70 backdrop-blur-sm"></div>
                    <motion.div initial={{ opacity: 0, scale: 0.9 }} animate={{ opacity: 1, scale: 1 }} exit={{ opacity: 0, scale: 0.9 }} className="booking-modal relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl border border-slate-700/50 shadow-2xl p-6" onClick={e => e.stopPropagation()}>
                        <button onClick={onClose} className="absolute top-4 right-4 w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-white transition-colors z-10"><i className="fas fa-times"></i></button>
                        {success ? (
                            <div className="text-center py-8">
                                <div className="w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl shadow-green-500/30"><i className="fas fa-check text-3xl text-white"></i></div>
                                <h3 className="text-2xl font-bold mb-3 gradient-text">{t('booking_submitted')}</h3>
                                <p className="text-slate-400 mb-4">{t('booking_received')} <span className="text-accent font-bold">#{(success.data && success.data.booking_id) || success.booking_id}</span> {t('for_car')} <span className="text-white font-semibold">{car.name || car.model}</span> {t('has_been_received')}</p>
                                <p className="text-slate-400 text-sm mb-8">{t('we_contact')}</p>
                                <button onClick={onClose} className="bg-gradient-to-r from-gold to-amber-400 text-primary px-8 py-3 rounded-full font-bold hover:shadow-xl hover:shadow-gold/30 transition-all">{t('close')}</button>
                            </div>
                        ) : (
                            <div>
                                <div className="flex items-center gap-4 mb-6 pb-4 border-b border-slate-700/50">
                                    <img src={car.image || (car.images && car.images[0]) || 'https://images.unsplash.com/photo-1549317661-bd32c8ce0afa?w=300&q=80'} alt={car.name || car.model} className="w-24 h-18 object-cover rounded-xl" onError={(e) => { e.target.onerror = null; e.target.src = 'https://images.unsplash.com/photo-1549317661-bd32c8ce0afa?w=300&q=80'; }} />
                                    <div><h3 className="text-xl font-bold">{car.name || car.model}</h3><p className="text-gold font-semibold">{parseFloat(car.price_per_day || car.daily_price || car.price || 0).toFixed(2)} MAD / day</p><p className="text-slate-400 text-sm">{calcDays()} · {t('total')}: <span className="text-accent font-bold">{totalPrice} MAD</span></p></div>
                                </div>
                                {error && <div className="bg-red-500/10 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl mb-4 text-sm"><i className="fas fa-exclamation-circle mr-2"></i>{error}</div>}
                                <form onSubmit={handleSubmit} className="space-y-4">
                                    <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('full_name')}</label><input type="text" name="customer_name" required value={form.customer_name} onChange={handleChange} className="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500" /></div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('customer_email')}</label><input type="email" name="customer_email" required value={form.customer_email} onChange={handleChange} className="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500" /></div>
                                        <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('customer_phone')}</label><input type="tel" name="customer_phone" required value={form.customer_phone} onChange={handleChange} className="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500" /></div>
                                    </div>
                                    <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('customer_city')}</label><input type="text" name="customer_city" value={form.customer_city} onChange={handleChange} className="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500" /></div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('start_date')}</label><input type="date" name="start_date" required value={form.start_date} onChange={handleChange} min={today} className="w-full px-4 py-3 rounded-xl text-white" /></div>
                                        <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('end_date')}</label><input type="date" name="end_date" required value={form.end_date} onChange={handleChange} min={form.start_date} className="w-full px-4 py-3 rounded-xl text-white" /></div>
                                    </div>
                                    <div><label className="block text-sm font-medium text-slate-300 mb-1.5">{t('notes')}</label><textarea name="notes" rows="3" value={form.notes} onChange={handleChange} className="w-full px-4 py-3 rounded-xl text-white placeholder-slate-500 resize-none"></textarea></div>
                                    <div className="bg-slate-800/50 rounded-xl p-4 border border-slate-700/50"><div className="flex justify-between items-center text-sm text-slate-400 mb-2"><span>{t('daily_rate')}</span><span>{parseFloat(car.price_per_day || car.daily_price || car.price || 0).toFixed(2)} MAD</span></div><div className="flex justify-between items-center text-sm text-slate-400 mb-2"><span>{t('number_of_days')}</span><span>{calcDays()}</span></div><div className="border-t border-slate-700/50 pt-2 mt-2"><div className="flex justify-between items-center font-bold"><span>{t('total')}</span><span className="gradient-text text-lg">{totalPrice} MAD</span></div></div></div>
                                    <button type="submit" disabled={loading} className="w-full bg-gradient-to-r from-gold to-amber-400 text-primary py-4 rounded-xl font-bold text-lg hover:shadow-xl hover:shadow-gold/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">{loading ? (<><i className="fas fa-spinner fa-spin"></i> {t('processing')}</>) : (<><i className="fas fa-check-circle"></i> {t('confirm_booking')}</>)}</button>
                                </form>
                            </div>
                        )}
                    </motion.div>
                </motion.div>
            );
        }

        function Fleet({ onBook }) {
            const { t } = useSite();
            const [cars, setCars] = useState([]); const [loading, setLoading] = useState(true); const [filter, setFilter] = useState('all');
            useEffect(() => { fetch('api/cars.php?action=available').then(res => res.json()).then(data => { setCars(Array.isArray(data) ? data : (data.data || [])); setLoading(false); }).catch(() => { setCars([]); setLoading(false); }); }, []);
            const categories = ['all', ...new Set(cars.map(c => c.type))];
            const filtered = filter === 'all' ? cars : cars.filter(c => c.type === filter);
            const getCategoryColor = (cat) => { switch(cat){case 'Sedan':return 'bg-green-500/10 text-green-400 border-green-500/30';case 'SUV':return 'bg-blue-500/10 text-blue-400 border-blue-500/30';case 'Hatchback':return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30';case 'Van':return 'bg-purple-500/10 text-purple-400 border-purple-500/30';case 'Truck':return 'bg-amber-500/10 text-amber-400 border-amber-500/30';default:return 'bg-slate-500/10 text-slate-400 border-slate-500/30';} };
            return (
                <section id="fleet" className="py-24 bg-[#0f172a]"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-16">
                        <div className="inline-flex items-center gap-2 bg-gold/10 border border-gold/30 rounded-full px-4 py-2 mb-4"><i className="fas fa-car-side text-gold text-sm"></i><span className="text-gold text-sm font-medium">{t('fleet_badge')}</span></div>
                        <h2 className="text-3xl md:text-4xl font-black mb-4">{t('fleet_title_1')} <span className="gradient-text">{t('fleet_title_2')}</span></h2>
                        <p className="text-slate-400 max-w-2xl mx-auto">{t('fleet_sub')}</p>
                    </motion.div>
                    <div className="flex flex-wrap justify-center gap-3 mb-12">{categories.map(cat => (<button key={cat} onClick={() => setFilter(cat)} className={`px-6 py-2.5 rounded-full font-medium transition-all capitalize ${filter === cat ? 'bg-gradient-to-r from-gold to-amber-400 text-primary' : 'bg-slate-800/50 text-slate-400 border border-slate-700/50 hover:border-gold/50 hover:text-gold'}`}>{cat === 'all' ? t('all_cars') : cat}</button>))}</div>
                    {loading ? (<div className="text-center py-16"><i className="fas fa-spinner fa-spin text-4xl text-gold mb-4"></i><p className="text-slate-400">{t('loading_fleet')}</p></div>) : (
                        <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">{filtered.map((car, index) => (
                            <motion.div key={car.id} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: index * 0.05 }} className="bg-slate-800/50 rounded-2xl border border-slate-700/50 overflow-hidden card-hover transition-all duration-300 group">
                                <div className="relative h-56 overflow-hidden"><img src={car.image || (car.images && car.images[0]) || 'https://images.unsplash.com/photo-1549317661-bd32c8ce0afa?w=400&q=80'} alt={car.name || car.model} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" onError={(e) => { e.target.onerror = null; e.target.src = 'https://images.unsplash.com/photo-1549317661-bd32c8ce0afa?w=400&q=80'; }} /><div className="absolute top-4 left-4"><span className={`px-3 py-1 rounded-full text-xs font-semibold border ${getCategoryColor(car.type)}`}>{(car.type || 'Car')}</span></div></div>
                                <div className="p-6"><h3 className="text-xl font-bold mb-2">{car.name || car.model}</h3><div className="flex items-center gap-4 text-sm text-slate-400 mb-4 flex-wrap"><span><i className="fas fa-user-friends mr-1"></i> {car.seats || 5} {t('seats')}</span><span><i className="fas fa-cog mr-1"></i> {car.transmission || 'Auto'}</span><span><i className="fas fa-gas-pump mr-1"></i> {car.fuel_type || 'Petrol'}</span></div><div className="flex items-center justify-between"><div><span className="text-2xl font-black gradient-text">{parseFloat(car.price_per_day || car.daily_price || car.price || 0).toFixed(0)}</span><span className="text-slate-400 text-sm"> MAD/day</span></div><button onClick={() => onBook(car)} className="bg-gradient-to-r from-accent to-cyan-400 text-primary px-6 py-2.5 rounded-full font-bold hover:shadow-lg hover:shadow-accent/30 transition-all">{t('book_now')}</button></div></div>
                            </motion.div>
                        ))}</div>
                    )}
                </div></section>
            );
        }

        function Services() { const { t } = useSite(); const services = [{ icon: 'fa-calendar-alt', title: t('svc1_t'), desc: t('svc1_d'), color: 'from-blue-500 to-cyan-500' }, { icon: 'fa-calendar-check', title: t('svc2_t'), desc: t('svc2_d'), color: 'from-gold to-amber-400' }, { icon: 'fa-plane-arrival', title: t('svc3_t'), desc: t('svc3_d'), color: 'from-green-500 to-emerald-500' }, { icon: 'fa-headset', title: t('svc4_t'), desc: t('svc4_d'), color: 'from-purple-500 to-pink-500' }]; return (
            <section id="services" className="py-24 bg-gradient-to-b from-[#1e293b] to-[#0f172a]"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-16"><div className="inline-flex items-center gap-2 bg-accent/10 border border-accent/30 rounded-full px-4 py-2 mb-4"><i className="fas fa-concierge-bell text-accent text-sm"></i><span className="text-accent text-sm font-medium">{t('services_badge')}</span></div><h2 className="text-3xl md:text-4xl font-black mb-4">{t('services_title_1')} <span className="gradient-text">{t('services_title_2')}</span></h2><p className="text-slate-400 max-w-2xl mx-auto">{t('services_sub')}</p></motion.div>
                <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">{services.map((s, i) => (<motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1 }} className="bg-slate-800/50 rounded-2xl border border-slate-700/50 p-8 card-hover transition-all duration-300 text-center group"><div className={`w-16 h-16 bg-gradient-to-br ${s.color} rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg group-hover:scale-110 transition-transform`}><i className={`fas ${s.icon} text-white text-xl`}></i></div><h3 className="text-xl font-bold mb-3">{s.title}</h3><p className="text-slate-400 text-sm leading-relaxed">{s.desc}</p></motion.div>))}</div>
            </div></section>
        ); }
        function WhyChooseUs() { const { t } = useSite(); const reasons = [{ icon: 'fa-shield-alt', title: t('why1_t'), desc: t('why1_d') }, { icon: 'fa-hand-holding-usd', title: t('why2_t'), desc: t('why2_d') }, { icon: 'fa-wrench', title: t('why3_t'), desc: t('why3_d') }, { icon: 'fa-bolt', title: t('why4_t'), desc: t('why4_d') }]; return (
            <section className="py-24 bg-[#0f172a]"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-16"><div className="inline-flex items-center gap-2 bg-gold/10 border border-gold/30 rounded-full px-4 py-2 mb-4"><i className="fas fa-trophy text-gold text-sm"></i><span className="text-gold text-sm font-medium">{t('why_badge')}</span></div><h2 className="text-3xl md:text-4xl font-black mb-4">{t('why_title_1')} <span className="gradient-text">{t('why_title_2')}</span></h2><p className="text-slate-400 max-w-2xl mx-auto">{t('why_sub')}</p></motion.div>
                <div className="grid md:grid-cols-2 lg:grid-cols-4 gap-8">{reasons.map((r, i) => (<motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.1 }} className="text-center p-8 rounded-2xl bg-gradient-to-b from-slate-800/50 to-slate-800/20 border border-slate-700/50 card-hover transition-all duration-300"><div className="w-16 h-16 bg-gradient-to-br from-gold to-amber-400 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-gold/20"><i className={`fas ${r.icon} text-primary text-xl`}></i></div><h3 className="text-lg font-bold mb-3">{r.title}</h3><p className="text-slate-400 text-sm leading-relaxed">{r.desc}</p></motion.div>))}</div>
            </div></section>
        ); }
        function Testimonials() { const { t, lang } = useSite(); const testimonials = lang === 'ar' ? [{ name: 'أحمد بنعلي', role: 'مسافر أعمال', text: 'خدمة ممتازة! السيارة كانت في حالة رائعة وعملية الحجز سهلة جداً.', avatar: 'https://i.pravatar.cc/100?img=11', rating: 5 }, { name: 'سارة المنصوري', role: 'سائحة', text: 'استأجرت سيارة لمدة أسبوع وكانت تجربة رائعة ومريحة.', avatar: 'https://i.pravatar.cc/100?img=32', rating: 5 }, { name: 'يوسف العلمي', role: 'عميل دائم', text: 'عميل منذ سنوات، خدمة موثوقة وسيارات جيدة دائماً.', avatar: 'https://i.pravatar.cc/100?img=53', rating: 5 }] : lang === 'fr' ? [{ name: 'Ahmed Benali', role: 'Voyageur d’affaires', text: 'Service exceptionnel ! Voiture en parfait état et réservation très simple.', avatar: 'https://i.pravatar.cc/100?img=11', rating: 5 }, { name: 'Sara El Mansouri', role: 'Touriste', text: 'Location parfaite pour une semaine, véhicule fiable et confortable.', avatar: 'https://i.pravatar.cc/100?img=32', rating: 5 }, { name: 'Youssef Alami', role: 'Client fidèle', text: 'Client depuis des années, toujours satisfait du service.', avatar: 'https://i.pravatar.cc/100?img=53', rating: 5 }] : [{ name: 'Ahmed Benali', role: 'Business Traveler', text: 'Exceptional service! The car was in perfect condition and booking was seamless.', avatar: 'https://i.pravatar.cc/100?img=11', rating: 5 }, { name: 'Sara El Mansouri', role: 'Tourist', text: 'Reliable, comfortable vehicle and fair prices for a week-long trip.', avatar: 'https://i.pravatar.cc/100?img=32', rating: 5 }, { name: 'Youssef Alami', role: 'Local Resident', text: 'Loyal customer for years. Outstanding support and well-maintained vehicles.', avatar: 'https://i.pravatar.cc/100?img=53', rating: 5 }]; return (
            <section className="py-24 bg-gradient-to-b from-[#0f172a] to-[#1e293b]"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-16"><div className="inline-flex items-center gap-2 bg-gold/10 border border-gold/30 rounded-full px-4 py-2 mb-4"><i className="fas fa-comments text-gold text-sm"></i><span className="text-gold text-sm font-medium">{t('test_badge')}</span></div><h2 className="text-3xl md:text-4xl font-black mb-4">{t('test_title_1')} <span className="gradient-text">{t('test_title_2')}</span></h2><p className="text-slate-400 max-w-2xl mx-auto">{t('test_sub')}</p></motion.div>
                <div className="grid md:grid-cols-3 gap-8">{testimonials.map((tt, i) => (<motion.div key={i} initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} transition={{ delay: i * 0.15 }} className="bg-slate-800/50 rounded-2xl border border-slate-700/50 p-8 card-hover transition-all duration-300"><div className="flex items-center gap-1 mb-4">{[...Array(tt.rating)].map((_, j) => (<i key={j} className="fas fa-star text-gold text-sm"></i>))}</div><p className="text-slate-300 mb-6 leading-relaxed italic">"{tt.text}"</p><div className="flex items-center gap-4"><img src={tt.avatar} alt={tt.name} className="w-12 h-12 rounded-full border-2 border-gold/30" /><div><h4 className="font-bold">{tt.name}</h4><p className="text-slate-400 text-sm">{tt.role}</p></div></div></motion.div>))}</div>
            </div></section>
        ); }
        function Contact() { const { t } = useSite(); const items = [{icon:'fab fa-whatsapp', grad:'from-green-500 to-green-600', title:t('whatsapp'), href:'https://wa.me/212764730187?text=Hello!%20I%27m%20interested%20in%20renting%20a%20car', label:'+212 7 64 73 01 87', sub:t('chat_direct'), text:'text-white'},{icon:'fas fa-phone-alt', grad:'from-accent to-cyan-400', title:t('phone'), href:'tel:+212615504273', label:'+212 6 15 50 42 73', sub:t('call_anytime'), text:'text-white'},{icon:'fab fa-instagram', grad:'from-purple-500 to-pink-500', title:'Instagram', href:'https://www.instagram.com/azzouz_brahim_car?igsh=MW9taDRhenk0Mmw0Mg==', label:'@azzouz_brahim_car', sub:t('follow_ig'), text:'text-white'},{icon:'fas fa-envelope', grad:'from-gold to-amber-400', title:t('email'), href:'mailto:info@ibrahimazzouzcars.com', label:'info@ibrahimazzouzcars.com', sub:t('send_email'), text:'text-primary'},{icon:'fas fa-map-marker-alt', grad:'from-blue-500 to-cyan-400', title:t('address'), href:'https://maps.app.goo.gl/kzU2eGq3kqTwzUPv7', label:t('city_country'), sub:t('visit_office'), text:'text-white'}]; return (
            <section id="contact" className="py-24 bg-[#0f172a]"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <motion.div initial={{ opacity: 0, y: 20 }} whileInView={{ opacity: 1, y: 0 }} viewport={{ once: true }} className="text-center mb-16"><div className="inline-flex items-center gap-2 bg-accent/10 border border-accent/30 rounded-full px-4 py-2 mb-4"><i className="fas fa-envelope text-accent text-sm"></i><span className="text-accent text-sm font-medium">{t('contact_badge')}</span></div><h2 className="text-3xl md:text-4xl font-black mb-4">{t('contact_title_1')} <span className="gradient-text">{t('contact_title_2')}</span></h2><p className="text-slate-400 max-w-2xl mx-auto">{t('contact_sub')}</p></motion.div>
                <div className="grid lg:grid-cols-2 gap-12"><motion.div initial={{ opacity: 0, x: -20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }}><div className="space-y-6">{items.map((c,i)=>(<div key={i} className="flex items-start gap-4 p-6 rounded-2xl bg-slate-800/50 border border-slate-700/50 card-hover transition-all duration-300"><div className={`w-14 h-14 bg-gradient-to-br ${c.grad} rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg`}><i className={`${c.icon} ${c.text} text-xl`}></i></div><div><h4 className="font-bold mb-1">{c.title}</h4><a href={c.href} target="_blank" rel="noopener noreferrer" className="text-accent hover:underline">{c.label}</a><p className="text-slate-400 text-sm mt-1">{c.sub}</p></div></div>))}</div></motion.div><motion.div initial={{ opacity: 0, x: 20 }} whileInView={{ opacity: 1, x: 0 }} viewport={{ once: true }}><div className="rounded-2xl overflow-hidden border border-slate-700/50 h-full min-h-[400px]"><iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3283.2!2d-5.58!3d35.58!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zTnVldCBvZiBUZXRvdWFu!5e0!3m2!1sen!2sma!4v1" width="100%" height="100%" style={{ border: 0, minHeight: '400px' }} allowFullScreen="" loading="lazy" referrerPolicy="no-referrer-when-downgrade" title="Map"></iframe></div></motion.div></div>
            </div></section>
        ); }
        function Footer() { const { t } = useSite(); const quickLinks=[{name:t('nav_home'),href:'#home'},{name:t('nav_about'),href:'#about'},{name:t('nav_fleet'),href:'#fleet'},{name:t('nav_services'),href:'#services'},{name:t('nav_contact'),href:'#contact'}]; const serviceLinks=[t('svc1_t'),t('svc2_t'),t('svc3_t'),t('svc4_t')]; return (
            <footer className="bg-[#0a0f1a] border-t border-slate-800/50"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16"><div className="grid md:grid-cols-2 lg:grid-cols-4 gap-12">
                <div><div className="flex items-center gap-3 mb-6"><div className="w-12 h-12 bg-gradient-to-br from-gold to-amber-400 rounded-xl flex items-center justify-center shadow-lg"><i className="fas fa-car text-primary text-xl font-bold"></i></div><div><span className="text-xl font-bold gradient-text">Ibrahim Azzouz</span><span className="block text-xs text-slate-400 -mt-1">{t('premium_rental')}</span></div></div><p className="text-slate-400 text-sm leading-relaxed mb-6">{t('footer_desc')}</p></div>
                <div><h4 className="text-lg font-bold mb-6">{t('quick_links')}</h4><ul className="space-y-3">{quickLinks.map((l,i)=>(<li key={i}><a href={l.href} className="text-slate-400 hover:text-gold transition-colors text-sm flex items-center gap-2"><i className="fas fa-chevron-right text-xs text-gold"></i> {l.name}</a></li>))}</ul></div>
                <div><h4 className="text-lg font-bold mb-6">{t('services_footer')}</h4><ul className="space-y-3">{serviceLinks.map((s,i)=>(<li key={i}><a href="#services" className="text-slate-400 hover:text-gold transition-colors text-sm flex items-center gap-2"><i className="fas fa-chevron-right text-xs text-gold"></i> {s}</a></li>))}</ul></div>
                <div><h4 className="text-lg font-bold mb-6">{t('contact_info')}</h4><ul className="space-y-4"><li className="flex items-start gap-3"><i className="fas fa-map-marker-alt text-gold mt-1"></i><a href="https://maps.app.goo.gl/kzU2eGq3kqTwzUPv7" target="_blank" rel="noopener noreferrer" className="text-slate-400 text-sm hover:text-accent transition-colors">{t('city_country')}</a></li><li className="flex items-start gap-3"><i className="fas fa-phone-alt text-gold mt-1"></i><a href="tel:+212615504273" className="text-slate-400 text-sm hover:text-accent transition-colors">+212 6 15 50 42 73</a></li><li className="flex items-start gap-3"><i className="fas fa-envelope text-gold mt-1"></i><a href="mailto:info@ibrahimazzouzcars.com" className="text-slate-400 text-sm hover:text-accent transition-colors">info@ibrahimazzouzcars.com</a></li></ul></div>
            </div></div><div className="border-t border-slate-800/50"><div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6"><div className="flex flex-col md:flex-row justify-between items-center gap-4"><p className="text-slate-500 text-sm">&copy; {new Date().getFullYear()} Ibrahim Azzouz Cars. {t('rights')}</p><p className="text-slate-500 text-sm">{t('made_with')} <i className="fas fa-heart text-red-500"></i> {t('in_tetouan')}</p></div></div></div></footer>
        ); }
        function FloatingButtons() { const [showTop, setShowTop] = useState(false); useEffect(() => { const handler = () => setShowTop(window.scrollY > 500); window.addEventListener('scroll', handler); return () => window.removeEventListener('scroll', handler); }, []); return (<><a href="https://wa.me/212764730187?text=Hello!%20I%27m%20interested%20in%20renting%20a%20car" target="_blank" rel="noopener noreferrer" className="fixed bottom-6 right-6 z-50 w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-full flex items-center justify-center shadow-xl shadow-green-500/30 hover:scale-110 transition-transform pulse-btn"><i className="fab fa-whatsapp text-white text-2xl"></i></a>{showTop && (<button onClick={() => window.scrollTo({ top: 0, behavior: 'smooth' })} className="fixed bottom-24 right-6 z-50 w-12 h-12 bg-gradient-to-br from-gold to-amber-400 rounded-full flex items-center justify-center shadow-xl shadow-gold/30 hover:scale-110 transition-transform"><i className="fas fa-arrow-up text-primary text-lg"></i></button>)}</>); }
        function App() { const [bookingCar, setBookingCar] = useState(null); const [bookingOpen, setBookingOpen] = useState(false); const openBooking = (car) => { setBookingCar(car); setBookingOpen(true); document.body.style.overflow = 'hidden'; }; const closeBooking = () => { setBookingOpen(false); setBookingCar(null); document.body.style.overflow = 'auto'; }; return (<SiteProvider><div><Navbar /><Hero /><About /><Fleet onBook={openBooking} /><Services /><WhyChooseUs /><Testimonials /><Contact /><Footer /><FloatingButtons /><AnimatePresence>{bookingOpen && (<BookingModal car={bookingCar} isOpen={bookingOpen} onClose={closeBooking} />)}</AnimatePresence></div></SiteProvider>); }
        const root = ReactDOM.createRoot(document.getElementById('root'));
        root.render(<App />);
    </script>
</body>
</html>
