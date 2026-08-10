<template>
    <footer class="relative bg-white dark:bg-[#09090b] border-t border-slate-200 dark:border-white/5 transition-colors duration-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">

            <!-- Back to Top Button (Light: Emerald | Dark: Orange) -->
            <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3">

                <!-- Back to Top Button -->
                <button 
                    v-show="showButton"
                    @click="scrollToTop" aria-label="Back to top"
                    class="h-10 w-10 rounded-full shadow-xl flex items-center justify-center transition-all duration-300 transform hover:-translate-y-1 active:scale-95
                        bg-emerald-600 hover:bg-emerald-700 dark:bg-orange-500 dark:hover:bg-orange-600 text-white">
                    <i class="fa-solid fa-angle-up text-sm" aria-hidden="true"></i>
                </button>
                
                <!-- WhatsApp Button -->
                <a 
                    href="https://wa.me/8801533021557" 
                    target="_blank" rel="noopener noreferrer"    aria-label="Chat with Ogrova on WhatsApp"    title="WhatsApp"
                    class="relative h-12 w-12 rounded-full shadow-lg flex items-center justify-center transition-all duration-300 transform hover:-translate-y-1 active:scale-95
                        bg-[#25D366] hover:bg-[#20bd5a] text-white">
                    
                    <!-- Pulse Effect Layers -->
                    <span aria-hidden="true" class="absolute inset-0 rounded-full bg-[#25D366] animate-ping opacity-40"></span>
                    <span aria-hidden="true" class="absolute inset-0 rounded-full bg-[#25D366] animate-pulse opacity-20"></span>
                    
                    <i class="fa-brands fa-whatsapp text-2xl relative z-10" aria-hidden="true"></i>
                </a>
            </div>

            <!-- 2. BIG FOOTER MAIN LINKS GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-8 pb-12 text-slate-600 dark:text-slate-400">
    
                <!-- 1. Profile & Contact Section (Takes 4 columns on large screens) -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Logo -->
                    <div class="flex items-center gap-2">
                        <div class="h-10 w-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200/60 dark:border-white/10 flex items-center justify-center transition-transform group-hover:scale-105">
                            <img src="/logo/logo.avif" alt="Logo" class="w-8 h-8 object-contain dark:hidden" />
                            <img src="/logo/white-logo.avif" alt="Logo" class="hidden dark:block w-8 h-8 object-contain" />
                        </div>
                        <span class="text-lg font-black text-slate-900 dark:text-white tracking-tight uppercase">
                            Ogro<span class="text-orange-500">v</span><span class="text-emerald-600">a</span> Pos
                        </span>
                    </div>
                    
                    <!-- Description -->
                    <p class="text-sm leading-relaxed text-slate-500 dark:text-slate-400 max-w-sm">
                        Your premium multi-brand e-commerce destination. Experience unmatched quality, ultra-fast delivery, and elite support tailored just for you.
                    </p>
                    
                    <!-- Contact Info (From image_690a3a.png layout) -->
                    <ul class="space-y-3 text-sm font-medium">
                        <li class="flex items-start gap-3">
                            <i class="fa-solid fa-location-dot text-base text-emerald-600 dark:text-orange-500 mt-0.5 w-4 text-center"></i>
                            <span>Khilkhet, Dhaka, Bangladesh</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-sm text-emerald-600 dark:text-orange-500 w-4 text-center"></i>
                            <a href="tel:+8801533021557" class="hover:text-emerald-600 dark:hover:text-orange-400 transition-colors">
                                +880 1533-021557
                            </a>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fa-solid fa-envelope text-sm text-emerald-600 dark:text-orange-500 w-4 text-center"></i>
                            <a href="mailto:contact@ghorerbazar.com" class="hover:text-emerald-600 dark:hover:text-orange-400 transition-colors truncate">info@ogrova.com</a>
                        </li>
                    </ul>

                    <!-- Socials -->
                    <div class="flex items-center gap-3 pt-1">
                        <a href="https://www.facebook.com/ogrova2026" target="_blank" rel="noopener noreferrer"  aria-label="Visit Ogrova on Facebook"  title="Facebook" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-all duration-300 hover:scale-105 hover:bg-emerald-600 hover:text-white dark:hover:bg-orange-500 hover:border-transparent shadow-sm"><i class="fa-brands fa-facebook-f text-xs"></i></a>
                        <!-- <a href="#" target="_blank" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-all duration-300 hover:scale-105 hover:bg-emerald-600 hover:text-white dark:hover:bg-orange-500 hover:border-transparent shadow-sm"><i class="fa-brands fa-x-twitter text-xs"></i></a> -->
                        <a href="https://www.instagram.com/ogrova2026/" target="_blank" rel="noopener noreferrer"  aria-label="Visit Ogrova on Instagram"  title="Instagram" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-all duration-300 hover:scale-105 hover:bg-emerald-600 hover:text-white dark:hover:bg-orange-500 hover:border-transparent shadow-sm"><i class="fa-brands fa-instagram text-xs"></i></a>
                        <a href="https://www.linkedin.com/company/143007240" target="_blank" rel="noopener noreferrer"  aria-label="Visit Ogrova on LinkedIn"  title="LinkedIn" class="h-8 w-8 rounded-full border border-slate-200 dark:border-slate-800 flex items-center justify-center text-slate-500 dark:text-slate-400 transition-all duration-300 hover:scale-105 hover:bg-emerald-600 hover:text-white dark:hover:bg-orange-500 hover:border-transparent shadow-sm"><i class="fa-brands fa-linkedin text-xs"></i></a>
                    </div>
                </div>

                <!-- 2. Information Column (Takes 2 columns) -->
                <div class="lg:col-span-2">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800/50 pb-2">Information</h4>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">About Us</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Contact Us</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Company Information</router-link></li>
                        <li><router-link to="/" aria-label="Terms and Conditions" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Terms & Conditions</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Privacy Policy</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Security</router-link></li>
                    </ul>
                </div>

                <!-- 3. Shop By Column (Takes 2 columns) -->
                <div class="lg:col-span-2">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800/50 pb-2">Shop By</h4>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Groceries & Fresh</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Premium Electronics</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Fashion & Apparel</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Nuts & Seeds</router-link></li>
                    </ul>
                </div>

                <!-- 4. Support Column (Takes 2 columns) -->
                <div class="lg:col-span-2">
                    <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800/50 pb-2">Support</h4>
                    <ul class="space-y-2.5 text-sm font-medium">
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Support Center</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">How to Order</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Track Your Order</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Payment Methods</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Shipping Info</router-link></li>
                        <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">FAQ</router-link></li>
                    </ul>
                </div>

                <!-- 5. Consumer Policy Column (Takes 2 columns) -->
                <div class="lg:col-span-2 space-y-5">
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800/50 pb-2">Consumer Policy</h4>
                        <ul class="space-y-2.5 text-sm font-medium">
                            <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Return & Refund</router-link></li>
                            <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Cancellation</router-link></li>
                            <li><router-link to="/" class="hover:translate-x-1 text-slate-500 dark:text-slate-400 transition-all duration-300 inline-block hover:text-emerald-600 dark:hover:text-orange-400">Exchange</router-link></li>
                        </ul>
                    </div>
                    
                    <!-- Download App On Mobile Section -->
                    <div class="pt-2">
                        <span class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-3">Download App</span>
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-2">
                            <!-- Google Play Store Button -->
                            <a href="#" class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white rounded-lg transition-all border border-transparent hover:border-slate-700 w-fit">
                                <i class="fa-brands fa-google-play text-lg"></i>
                                <div class="text-left">
                                    <p class="text-[9px] uppercase tracking-tight text-slate-400 font-medium leading-none">Get it on</p>
                                    <p class="text-xs font-bold font-sans tracking-wide leading-tight">Google Play</p>
                                </div>
                            </a>
                            <!-- App Store Button -->
                            <a href="#" class="flex items-center gap-2 px-3 py-1.5 bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white rounded-lg transition-all border border-transparent hover:border-slate-700 w-fit">
                                <i class="fa-brands fa-apple text-xl"></i>
                                <div class="text-left">
                                    <p class="text-[9px] uppercase tracking-tight text-slate-400 font-medium leading-none">Download on the</p>
                                    <p class="text-xs font-bold font-sans tracking-wide leading-tight">App Store</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        
            <!-- Top Row: Identity & Live System Status -->
            <div class="flex flex-col lg:flex-row justify-between items-center gap-6 pb-6 border-b border-slate-200/60 dark:border-white/5">
                
                <!-- Logo & Text -->
                <div class="flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
                    <router-link to="/" target="_blank" aria-label="Go to homepage" class="flex items-center gap-3 group">
                        <div class="h-10 w-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200/60 dark:border-white/10 flex items-center justify-center transition-transform group-hover:scale-105">
                            <img src="/logo/logo.avif" alt="Ogrova Logo" class="w-8 h-8 object-contain dark:hidden" />
                            <img src="/logo/white-logo.avif" alt="Ogrova Logo" class="hidden dark:block w-8 h-8 object-contain" />
                        </div>
                        <span class="text-base font-black tracking-tight text-slate-900 dark:text-white uppercase">
                            Ogro<span class="text-orange-500">v</span><span class="text-emerald-600">a</span> POS
                        </span>
                    </router-link>
                </div>

                <!-- Status Badge & Social Networks -->
                <div class="flex flex-col sm:flex-row items-center gap-6 w-full sm:w-auto justify-center">
                    <div class="flex items-center gap-2.5 px-4 py-2 rounded-full bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 shrink-0">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-slate-500 dark:text-slate-400">
                            System: <span class="text-emerald-500">Active</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-5 text-slate-400 dark:text-slate-500">
                        <a href="https://www.facebook.com/ogrova2026" target="_blank" rel="noopener noreferrer"  aria-label="Visit Ogrova on Facebook"  title="Facebook" class="hover:text-emerald-600 dark:hover:text-orange-500 transition-colors text-sm"><i class="fa-brands fa-facebook" aria-hidden="true"></i></a>
                        <a href="https://www.linkedin.com/company/143007240" target="_blank" rel="noopener noreferrer" aria-label="Visit Ogrova on LinkedIn" title="Instagram" class="hover:text-emerald-600 dark:hover:text-orange-500 transition-colors text-sm"><i class="fa-brands fa-linkedin-in" aria-hidden="true"></i></a>
                        <a href="https://www.instagram.com/ogrova2026/" target="_blank" rel="noopener noreferrer" aria-label="Visit Ogrova on Instagram"  title="LinkedIn" class="hover:text-emerald-600 dark:hover:text-orange-500 transition-colors text-sm"><i class="fa-brands fa-instagram" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

            <!-- Merged Bottom Row: Copyrights & Gateway Panel -->
            <div class="pt-6 flex flex-col md:flex-row items-center justify-between gap-4 text-xs font-medium text-slate-400 dark:text-slate-500">
                
                <!-- Copyright & Powered by text -->
                <div class="flex flex-col sm:flex-row items-center justify-center md:justify-start gap-2.5 sm:gap-4 flex-wrap text-center md:text-left">
                    <p>© {{ new Date().getFullYear() }} <a href="https://mercuviax.com" target="_blank" class="hover:text-emerald-600 dark:hover:text-orange-400 font-bold transition-colors">Ogrova</a>. All Rights Reserved.</p>
                    <div class="hidden sm:block h-3.5 w-px bg-slate-200 dark:bg-slate-800"></div>
                    <p>Powered By: <a href="https://mercuviax.com" target="_blank" class="hover:text-emerald-600 dark:hover:text-orange-400 transition-colors font-bold italic">Mercuviax Software</a>.</p>
                </div>

                <!-- Payment Partners Premium Mock Icons -->
                <div class="flex items-center gap-4 text-xl opacity-70 dark:opacity-50 select-none">
                    <i class="fa-brands fa-cc-visa transition-all duration-300 cursor-pointer hover:scale-110 hover:opacity-100 hover:text-emerald-600 dark:hover:text-orange-500"></i>
                    <i class="fa-brands fa-cc-mastercard transition-all duration-300 cursor-pointer hover:scale-110 hover:opacity-100 hover:text-emerald-600 dark:hover:text-orange-500"></i>
                    <i class="fa-brands fa-cc-paypal transition-all duration-300 cursor-pointer hover:scale-110 hover:opacity-100 hover:text-emerald-600 dark:hover:text-orange-500"></i>
                    <i class="fa-brands fa-cc-apple-pay transition-all duration-300 cursor-pointer hover:scale-110 hover:opacity-100 hover:text-emerald-600 dark:hover:text-orange-500"></i>
                </div>
            </div>

        </div>
    </footer>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const showButton = ref(false)

const handleScroll = () => {
    showButton.value = window.scrollY > 300
}

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    })
}

onMounted(() => { window.addEventListener('scroll', handleScroll) })
onUnmounted(() => { window.removeEventListener('scroll', handleScroll) })
</script>

<style scoped>
/* Subtle Dark Mode Glow Match */
.dark footer {
    box-shadow: 0 -10px 40px -20px rgba(249, 115, 22, 0.04);
}
</style>