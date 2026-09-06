<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import { onMounted, onUnmounted } from 'vue';

// Import custom styles
import '@/../css/welcome_assets.css';

/* ---------- Scroll-reveal (Intersection Observer) ---------- */
let observer: IntersectionObserver | null = null;

onMounted(() => {
    // Reveal elements on scroll
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
    );

    document.querySelectorAll('.reveal').forEach((el) => observer!.observe(el));

    // Header scroll shadow
    const header = document.querySelector('.cwa-header');
    const onScroll = () => {
        if (window.scrollY > 20) header?.classList.add('scrolled');
        else header?.classList.remove('scrolled');
    };
    window.addEventListener('scroll', onScroll, { passive: true });

    // Cleanup listener on unmount via closure
    (window as any).__welcomeScrollCleanup = () => window.removeEventListener('scroll', onScroll);
});

onUnmounted(() => {
    observer?.disconnect();
    (window as any).__welcomeScrollCleanup?.();
});
</script>

<template>

    <Head title="Welcome - CWA Toolkit">
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
            rel="stylesheet" />
    </Head>

    <div class="welcome-page min-h-screen bg-white">

        <!-- ==================== HEADER ==================== -->
        <header class="cwa-header">
            <div class="header-inner">
                <div class="logo-group">
                    <img src="/logo-kemenkeu-smi-wb.png" alt="Kemenkeu & SMI Logos" />
                </div>
                <nav>
                    <Link v-if="$page.props.auth.user" :href="dashboard().url" class="btn-primary-cwa">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect width="7" height="9" x="3" y="3" rx="1" />
                            <rect width="7" height="5" x="14" y="3" rx="1" />
                            <rect width="7" height="9" x="14" y="12" rx="1" />
                            <rect width="7" height="5" x="3" y="16" rx="1" />
                        </svg>
                        DASHBOARD
                    </Link>
                    <Link v-else :href="login().url" class="btn-primary-cwa">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4" />
                            <polyline points="10 17 15 12 10 7" />
                            <line x1="15" x2="3" y1="12" y2="12" />
                        </svg>
                        MASUK
                    </Link>
                </nav>
            </div>
        </header>

        <!-- ==================== HERO ==================== -->
        <section class="hero-section">
            <!-- Background Effects -->
            <div class="hero-particles">
                <span class="hero-particle"></span>
                <span class="hero-particle"></span>
                <span class="hero-particle"></span>
                <span class="hero-particle"></span>
                <span class="hero-particle"></span>
                <span class="hero-particle"></span>
            </div>
            <div class="hero-blob hero-blob-1"></div>
            <div class="hero-blob hero-blob-2"></div>
            <div class="hero-geo-accent"></div>

            <div class="hero-inner">
                <!-- Text Column -->
                <div class="hero-text-col">
                    <div class="hero-badge hero-enter d1">
                        <span class="badge-dot"></span>
                        Self Assessment
                    </div>

                    <h1 class="hero-title hero-enter d2">
                        <span class="highlight">CWA</span> Toolkit
                    </h1>

                    <div class="hero-subtitle-tag hero-enter d3">
                        Penilaian Mandiri Kelayakan Kredit
                    </div>

                    <p class="hero-description hero-enter d4">
                        Platform penilaian mandiri untuk mengukur kelayakan kredit daerah. Mendukung kemandirian fiskal
                        dan percepatan pembangunan infrastruktur melalui analisis yang komprehensif.
                    </p>

                    <div class="hero-enter d5" style="margin-top: 1.5rem;">
                        <Link v-if="$page.props.auth.user" :href="dashboard().url" class="btn-primary-cwa"
                            style="font-size: 0.85rem; padding: 0.75rem 2.25rem;">
                            Mulai Assessment →
                        </Link>
                        <Link v-else :href="login().url" class="btn-primary-cwa"
                            style="font-size: 0.85rem; padding: 0.75rem 2.25rem;">
                            Mulai Assessment →
                        </Link>
                    </div>
                </div>

                <!-- Image Column -->
                <div class="hero-image-col hero-enter d4">
                    <div class="hero-image-wrapper">
                        <div class="hero-image-ring"></div>
                        <div class="hero-image-ring"></div>
                        <img src="/assets/images/hero.png" alt="CWA Toolkit Illustration" />
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== LATAR BELAKANG ==================== -->
        <section class="bg-section">
            <div class="section-inner">
                <!-- Section header -->
                <div class="reveal" style="text-align: center; margin-bottom: 3rem;">
                    <div class="section-label" style="justify-content: center;">
                        <span class="label-line"></span>
                        Konteks
                        <span class="label-line" style="transform: scaleX(-1);"></span>
                    </div>
                    <h2 class="section-heading">Latar Belakang</h2>
                </div>

                <div class="content-grid">
                    <!-- Text Column -->
                    <div class="text-col stagger-children">
                        <div class="bullet-card reveal from-left">
                            <span class="bullet-number">01</span>
                            <p>Kapasitas fiskal daerah yang kuat merupakan fondasi utama bagi percepatan pembangunan dan
                                peningkatan kualitas pelayanan publik.</p>
                        </div>

                        <div class="bullet-card reveal from-left">
                            <span class="bullet-number">02</span>
                            <p>Ketergantungan pada Dana Transfer dari pemerintah pusat sering kali menjadi tantangan.
                                Hal ini mendorong Pemerintah Daerah (Pemda) untuk mencari sumber-sumber pembiayaan
                                alternatif yang lebih inovatif dan berkelanjutan.</p>
                        </div>

                        <div class="bullet-card reveal from-left">
                            <span class="bullet-number">03</span>
                            <p>Untuk mengakses pembiayaan alternatif, seperti pinjaman daerah atau produk pembiayaan
                                Infrastruktur, Pemda perlu memiliki kelayakan kredit (creditworthiness) yang memadai.
                            </p>
                        </div>
                    </div>

                    <!-- Image Column -->
                    <div class="image-col">
                        <div class="image-showcase reveal from-right">
                            <img src="/assets/images/construction.png" alt="Infrastructure Construction" />
                            <div class="image-overlay"></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== TUJUAN ==================== -->
        <section class="goals-section" :style="{ backgroundImage: 'url(/assets/images/goals.png)' }">
            <div class="section-inner">
                <!-- Section header -->
                <div class="reveal" style="text-align: center; margin-bottom: 3rem;">
                    <div class="section-label" style="justify-content: center;">
                        <span class="label-line"></span>
                        Sasaran
                        <span class="label-line" style="transform: scaleX(-1);"></span>
                    </div>
                    <h2 class="section-heading section-heading-light">Tujuan</h2>
                </div>

                <div class="goals-grid stagger-children">
                    <!-- Card 1 -->
                    <div class="goal-card reveal scale-in">
                        <div class="goal-icon blue">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z" />
                                <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z" />
                            </svg>
                        </div>
                        <h3>Pemahaman</h3>
                        <p>Membekali Pemda dengan pemahaman mendalam mengenai posisi keuangan daerah serta
                            langkah-langkah perbaikannya.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="goal-card reveal scale-in">
                        <div class="goal-icon yellow">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 20h9" />
                                <path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z" />
                            </svg>
                        </div>
                        <h3>Penyiapan</h3>
                        <p>Mendorong kesiapan Pemda dalam mengakses pembiayaan alternatif, termasuk pinjaman daerah dan
                            produk pembiayaan infrastruktur berkelanjutan.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="goal-card reveal scale-in">
                        <div class="goal-icon sky">
                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                                <path d="m9 12 2 2 4-4" />
                            </svg>
                        </div>
                        <h3>Penguatan</h3>
                        <p>Memperkuat literasi fiskal dan tata kelola risiko sebagai bagian dari upaya meningkatkan
                            kelayakan kredit daerah.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ==================== FOOTER ==================== -->
        <footer class="footer-section">
            <div class="section-inner">
                <div class="footer-grid">
                    <!-- Mobile Image -->
                    <div class="footer-image-col reveal from-left">
                        <img src="/assets/images/mobile.png" alt="CWA Mobile Application" />
                    </div>

                    <!-- Contact Info -->
                    <div class="footer-text-col reveal from-right">
                        <h3>Terima Kasih 🙏</h3>

                        <div class="footer-info">
                            <p><strong>Badan Pendidikan dan Pelatihan Keuangan</strong></p>
                            <p><strong>Pusat Pendidikan dan Pelatihan Anggaran dan Perbendaharaan</strong></p>
                        </div>

                        <div class="footer-info" style="margin-top: 1rem;">
                            <p>Jl. Raya Puncak No.KM 72 Ds. Gadog,</p>
                            <p>Kec. Megamendung, Kabupaten Bogor, Jawa Barat 16720</p>
                        </div>

                        <div style="margin-top: 1.5rem;">
                            <p style="font-weight: 700; color: #004799; margin-bottom: 0.5rem; font-size: 0.85rem;">
                                Ikuti Kami</p>
                            <div class="social-chips">
                                <span class="social-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                                    </svg>
                                    pusdiklat anggaran dan perbendaharaan
                                </span>
                                <span class="social-chip">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
                                        fill="currentColor">
                                        <path
                                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                                    </svg>
                                    @pusdiklatap
                                </span>
                            </div>
                        </div>

                        <p style="margin-top: 1.5rem; font-size: 0.875rem; color: #475569;">
                            Kunjungi
                            <a href="https://klc2.kemenkeu.go.id" target="_blank"
                                style="color: #FFB800; font-weight: 700; text-decoration: underline; text-underline-offset: 3px;">
                                klc2.kemenkeu.go.id
                            </a>
                            untuk mengikuti pelatihan lainnya!
                        </p>
                    </div>
                </div>
            </div>

            <!-- Bottom bar -->
            <div class="footer-bottom">
                <p>&copy; {{ new Date().getFullYear() }} crafted with ❤️ <a href="https://klc2.kemenkeu.go.id"
                        target="_blank">@ptpkemenkeu</a></p>
            </div>
        </footer>

    </div>
</template>

<style scoped>
/* Specific overrides if needed */
</style>
