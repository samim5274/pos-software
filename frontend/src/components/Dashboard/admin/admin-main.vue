<template>
    <main class="min-h-screen bg-gray-50 dark:bg-[#0C1326]">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 py-5">

            <!-- Top bar (Title + optional actions/search) -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 dark:text-gray-100">Dashboard Overview</h1>
                    <p class="text-sm text-gray-700 dark:text-slate-300">Ogrova Online Business summary & performance</p>
                </div>

                <!-- Optional: right side button -->
                <div class="flex items-center gap-3">
                    <button class="rounded-xl px-4 py-2 text-sm font-semibold bg-slate-200 dark:bg-white/10 
                    dark:text-white border border-white/10 hover:bg-white/15 transition">Export</button>
                </div>
            </div>

            <!-- Loading & Error Alert -->
            <div v-if="loading" class="mt-4 text-center py-4 text-slate-500">
                <!-- Dashboard Skeleton Loader -->
                <div class="space-y-8 mb-8 animate-pulse">

                    <!-- ================= SECTION 1: PRIMARY FINANCIAL OVERVIEW SKELETON ================= -->
                    <div class="space-y-6">
                        <!-- 4 Big Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div v-for="i in 4" :key="'fin-main-' + i" class="rounded-2xl border border-slate-200/60 bg-white dark:bg-slate-900 dark:border-slate-800/80 p-6 shadow-sm">
                                <div class="flex flex-col justify-between min-h-[140px]">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-9 w-9 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="h-3.5 w-20 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        </div>
                                        <div class="h-4 w-12 bg-slate-200 dark:bg-slate-800 rounded-md"></div>
                                    </div>
                                    <!-- Value -->
                                    <div class="my-4 flex items-center gap-2">
                                        <div class="h-6 w-5 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        <div class="h-8 w-32 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    </div>
                                    <!-- Footer -->
                                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-3">
                                        <div class="h-3 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        <div class="h-3 w-12 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 4 Mini Analytic Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div v-for="i in 4" :key="'fin-sub-' + i" class="rounded-xl border border-slate-200/60 bg-white dark:bg-slate-900/40 dark:border-slate-800/60 p-5">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 rounded-lg bg-slate-200 dark:bg-slate-800"></div>
                                    <div class="space-y-2 flex-1">
                                        <div class="h-3 w-20 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        <div class="h-5 w-28 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= SECTION 2: PAYMENT & GATEWAY METRICS SKELETON ================= -->
                    <div class="space-y-4 pt-4">
                        <!-- Section Title Skeleton -->
                        <div class="space-y-2">
                            <div class="h-5 w-52 bg-slate-200 dark:bg-slate-800 rounded"></div>
                            <div class="h-3 w-72 bg-slate-200 dark:bg-slate-800 rounded"></div>
                        </div>

                        <!-- Dynamic Payment Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                            <div v-for="i in 8" :key="'pay-card-' + i" class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 p-5">
                                <div class="flex flex-col justify-between min-h-[120px]">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="h-10 w-10 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="h-3.5 w-16 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        </div>
                                        <div class="h-4 w-10 bg-slate-200 dark:bg-slate-800 rounded-md"></div>
                                    </div>
                                    <!-- Value -->
                                    <div class="my-3">
                                        <div class="h-8 w-24 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    </div>
                                    <!-- Footer -->
                                    <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-2.5">
                                        <div class="h-3 w-28 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        <div class="h-3 w-3 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ================= SECTION 3: ORDER DETAILS SKELETON ================= -->
                    <div class="space-y-4 pt-4">
                        <!-- Section Title Skeleton -->
                        <div class="space-y-2">
                            <div class="h-5 w-36 bg-slate-200 dark:bg-slate-800 rounded"></div>
                            <div class="h-3 w-64 bg-slate-200 dark:bg-slate-800 rounded"></div>
                        </div>

                        <!-- Order Status Cards Grid (3 Columns) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div v-for="i in 6" :key="'order-card-' + i" class="rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-6">
                                <div class="flex flex-col justify-between min-h-[140px]">
                                    <!-- Header -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                            <div class="h-3.5 w-24 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                        </div>
                                        <div class="h-4 w-12 bg-slate-200 dark:bg-slate-800 rounded-md"></div>
                                    </div>
                                    <!-- Value -->
                                    <div class="my-4">
                                        <div class="h-9 w-20 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                    </div>
                                    <!-- Footer -->
                                    <div class="flex items-center gap-2 border-t border-slate-100 dark:border-slate-800/40 pt-3">
                                        <div class="h-3 w-3 bg-slate-200 dark:bg-slate-800 rounded-full"></div>
                                        <div class="h-3 w-32 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div v-if="errorMsg" class="mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-600 text-sm">
                {{ errorMsg }}
            </div>


            <!-- =========================== Balance Section =========================== -->
            <div class="space-y-6 mb-8">

                <div class="space-y-8">
                    
                    <!-- ================= SECTION 1: PRIMARY FINANCIAL OVERVIEW ================= -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        
                        <!-- CARD 1: TOTAL SALES (AMBER ACCENT) -->
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all duration-300 hover:border-amber-200 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900 dark:hover:border-amber-500/30">
                            <div class="relative flex h-full flex-col justify-between min-h-[140px]">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-50 border border-amber-100 text-amber-600 dark:bg-amber-500/10 dark:border-amber-500/20 dark:text-amber-400">
                                            <i class="fa-solid fa-cart-shopping text-sm"></i>
                                        </div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Sales</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-700 border border-amber-200/40 dark:bg-amber-500/10 dark:text-amber-400 dark:border-transparent">Gross</span>
                                </div>
                                <div class="my-3 flex items-baseline text-slate-900 dark:text-white">
                                    <span class="text-2xl font-medium text-slate-400 dark:text-slate-500 mr-1.5">৳</span>
                                    <h2 class="font-mono text-3xl font-extrabold tracking-tight">{{ (formatCount(summary.total_sales) || 0).toLocaleString() }}</h2>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800/50">
                                    <span class="text-[11px] font-medium text-slate-400">Total order amount</span>
                                    <a href="#" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                        <span>Details</span>
                                        <svg class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 2: TOTAL REVENUE (PURPLE ACCENT) -->
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all duration-300 hover:border-purple-200 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900 dark:hover:border-purple-500/30">
                            <div class="relative flex h-full flex-col justify-between min-h-[140px]">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-purple-50 border border-purple-100 text-purple-600 dark:bg-purple-500/10 dark:border-purple-500/20 dark:text-purple-400">
                                            <i class="fa-solid fa-hand-holding-dollar text-sm"></i>
                                        </div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Revenue</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md bg-purple-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-purple-700 border border-purple-200/40 dark:bg-purple-500/10 dark:text-purple-400 dark:border-transparent">Payable</span>
                                </div>

                                <div class="mt-3 flex items-baseline text-slate-900 dark:text-white">
                                    <span class="text-2xl font-medium text-slate-400 dark:text-slate-500 mr-1.5">৳</span>
                                    <h2 class="font-mono text-3xl font-extrabold tracking-tight">{{ (formatCount(summary.total_revenue) || 0).toLocaleString() }}</h2>
                                </div>

                                <div class="mb-3 flex items-center justify-between text-[11px] text-slate-400 dark:text-slate-500">
                                    <span>Discount Applied</span>
                                    <span class="font-mono font-medium text-amber-600 dark:text-amber-400">
                                        - ৳{{ (summary.total_discount || 0).toLocaleString() }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800/50">
                                    <span class="text-[11px] font-medium text-slate-400">Net revenue</span>
                                    <a href="#" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                        <span>Details</span>
                                        <svg class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 3: TOTAL PAID AMOUNT (INDIGO ACCENT) -->
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all duration-300 hover:border-indigo-200 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900 dark:hover:border-indigo-500/30">
                            <div class="relative flex h-full flex-col justify-between min-h-[140px]">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-600 dark:bg-indigo-500/10 dark:border-indigo-500/20 dark:text-indigo-400">
                                            <i class="fa-solid fa-wallet text-sm"></i>
                                        </div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Paid Amount</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-indigo-700 border border-indigo-200/40 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-transparent">Received</span>
                                </div>
                                <div class="my-3 flex items-baseline text-slate-900 dark:text-white">
                                    <span class="text-2xl font-medium text-slate-400 dark:text-slate-500 mr-1.5">৳</span>
                                    <h2 class="font-mono text-3xl font-extrabold tracking-tight">{{ (formatCount(summary.total_paid_amount) || 0).toLocaleString() }}</h2>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800/50">
                                    <span class="text-[11px] font-medium text-slate-400">Collected balance</span>
                                    <a href="#" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                        <span>Details</span>
                                        <svg class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 4: TOTAL DUE AMOUNT (ROSE ACCENT) -->
                        <div class="group relative overflow-hidden rounded-2xl border border-slate-200/60 bg-white p-6 shadow-[0_8px_30px_rgb(0,0,0,0.02)] transition-all duration-300 hover:border-rose-200 hover:shadow-md dark:border-slate-800/80 dark:bg-slate-900 dark:hover:border-rose-500/30">
                            <div class="relative flex h-full flex-col justify-between min-h-[140px]">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 border border-rose-100 text-rose-600 dark:bg-rose-500/10 dark:border-rose-500/20 dark:text-rose-400">
                                            <i class="fa-solid fa-file-invoice-dollar text-sm"></i>
                                        </div>
                                        <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Due</span>
                                    </div>
                                    <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-rose-700 border border-rose-200/40 dark:bg-rose-500/10 dark:text-rose-400 dark:border-transparent">Receivable</span>
                                </div>
                                <div class="my-3 flex items-baseline text-slate-900 dark:text-white">
                                    <span class="text-2xl font-medium text-slate-400 dark:text-slate-500 mr-1.5">৳</span>
                                    <h2 class="font-mono text-3xl font-extrabold tracking-tight">{{ (formatCount(summary.total_due_amount) || 0).toLocaleString() }}</h2>
                                </div>
                                <div class="flex items-center justify-between border-t border-slate-100 pt-3 dark:border-slate-800/50">
                                    <span class="text-[11px] font-medium text-slate-400">Pending payment</span>
                                    <a href="#" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                                        <span>Statement</span>
                                        <svg class="h-3 w-3 transition-transform duration-200 group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- ================= SECTION 2: ADDITIONAL BREAKDOWN ANALYTICS ================= -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                                            
                        <!-- CARD 5: SHIPPING CHARGES (SKY BLUE ACCENT) -->
                        <div class="group relative overflow-hidden rounded-xl border border-slate-200/60 bg-white p-5 transition-all duration-300 hover:border-sky-200 hover:shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40 dark:hover:border-sky-500/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-sky-50 border border-sky-100 text-sky-600 dark:bg-sky-500/10 dark:border-transparent dark:text-sky-400">
                                    <i class="fa-solid fa-truck text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Shipping Charge</p>
                                    <h4 class="font-mono text-xl font-extrabold text-slate-800 dark:text-slate-200 mt-0.5">
                                        <span class="text-slate-400 dark:text-slate-500 font-sans text-lg font-medium mr-0.5">৳</span>{{ (formatCount(summary.total_shipping_charge) || 0).toLocaleString() }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 6: TOTAL TAX (CYAN ACCENT) -->
                        <div class="group relative overflow-hidden rounded-xl border border-slate-200/60 bg-white p-5 transition-all duration-300 hover:border-cyan-200 hover:shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40 dark:hover:border-cyan-500/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-cyan-50 border border-cyan-100 text-cyan-600 dark:bg-cyan-500/10 dark:border-transparent dark:text-cyan-400">
                                    <i class="fa-solid fa-receipt text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Total Tax</p>
                                    <h4 class="font-mono text-xl font-extrabold text-slate-800 dark:text-slate-200 mt-0.5">
                                        <span class="text-slate-400 dark:text-slate-500 font-sans text-lg font-medium mr-0.5">৳</span>{{ (formatCount(summary.total_tax) || 0).toLocaleString() }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 7: COUPON DISCOUNT (EMERALD ACCENT) -->
                        <div class="group relative overflow-hidden rounded-xl border border-slate-200/60 bg-white p-5 transition-all duration-300 hover:border-emerald-200 hover:shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40 dark:hover:border-emerald-500/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:border-transparent dark:text-emerald-400">
                                    <i class="fa-solid fa-ticket text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Coupon Discount</p>
                                    <h4 class="font-mono text-xl font-extrabold text-slate-800 dark:text-slate-200 mt-0.5">
                                        <span class="text-slate-400 dark:text-slate-500 font-sans text-lg font-medium mr-0.5">৳</span>{{ (formatCount(summary.total_coupon_discount) || 0).toLocaleString() }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <!-- CARD 8: TOTAL POINTS (TEAL ACCENT) -->
                        <div class="group relative overflow-hidden rounded-xl border border-slate-200/60 bg-white p-5 transition-all duration-300 hover:border-teal-200 hover:shadow-sm dark:border-slate-800/60 dark:bg-slate-900/40 dark:hover:border-teal-500/20">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 border border-teal-100 text-teal-600 dark:bg-teal-500/10 dark:border-transparent dark:text-teal-400">
                                    <i class="fa-solid fa-star text-base"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400 dark:text-slate-500">Earned Points</p>
                                    <h4 class="font-mono text-xl font-extrabold text-slate-800 dark:text-slate-200 mt-0.5">
                                        {{ (formatCount(summary.total_points) || 0).toLocaleString() }}
                                    </h4>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
            <!-- =========================== Balance Section =========================== -->





            <div class="mt-8 space-y-4">
                <!-- Header Title -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Payment & Gateway Metrics</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Real-time breakdown of transaction channels and gateways</p>
                    </div>
                </div>

                <!-- Cards Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div 
                        v-for="card in paymentCards" 
                        :key="card.title" 
                        class="group relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900 p-5 shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all duration-300 hover:-translate-y-1 hover:shadow-lg dark:hover:shadow-none"
                        :class="card.hoverBorder"
                    >
                        <div class="flex flex-col justify-between min-h-[120px]">
                            
                            <!-- Card Header (Title & Badge) -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div :class="['flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-transparent transition-transform duration-300 group-hover:scale-105', card.iconWrap]">
                                        <i :class="[card.icon, 'text-base']"></i>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        {{ card.title }}
                                    </span>
                                </div>

                                <span :class="['inline-flex items-center rounded-md px-2 py-0.5 text-[10px] font-extrabold uppercase tracking-wider border', card.pillClass]">
                                    {{ card.pill }}
                                </span>
                            </div>

                            <!-- Card Value -->
                            <div class="my-3 flex items-baseline text-slate-900 dark:text-white">
                                <h2 class="font-mono text-3xl font-extrabold tracking-tight">
                                    {{ formatCount(card.value) }}
                                </h2>
                            </div>

                            <!-- Footer / Subtext -->
                            <div class="flex items-center justify-between border-t border-slate-100 dark:border-slate-800/60 pt-2.5">
                                <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                                    {{ card.subtext }}
                                </span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-300 dark:text-slate-600 transition-opacity opacity-0 group-hover:opacity-100"></i>
                            </div>

                        </div>
                    </div>
                </div>
            </div>





            <!-- =========================== Card Section (Dynamic Order Statuses) =========================== -->
            <div class="mt-8 space-y-4">
                <!-- Header Title -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">Order Details</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Real-time breakdown of transaction channels and gateways</p>
                    </div>
                </div>
            
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <div 
                        v-for="card in statusCards" 
                        :key="card.key" 
                        class="group relative overflow-hidden rounded-xl border border-slate-300 dark:border-slate-800 bg-white p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-100 dark:border-slate-800/80 dark:bg-slate-900 dark:hover:shadow-none">
                        <div class="flex h-full flex-col justify-between min-h-[140px]">
                        
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div :class="['flex h-10 w-10 items-center justify-center rounded-xl border border-transparent', card.iconWrap]">
                                        <i :class="card.icon" class="text-base"></i>
                                    </div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                        {{ card.title }}
                                    </span>
                                </div>
                                <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-0.5 text-[10px] font-bold text-slate-500 border border-slate-100 dark:bg-slate-800/50 dark:text-slate-400 dark:border-transparent">
                                    {{ card.pill }}
                                </span>
                            </div>

                            <div class="my-4 flex items-baseline text-slate-900 dark:text-white">
                                <h2 class="font-mono text-3xl font-black tracking-tight">
                                    {{ formatCount(card.value) }}
                                </h2>
                            </div>

                            <div class="flex items-center gap-1.5 border-t border-slate-50 pt-3 dark:border-slate-800/40 text-slate-800 dark:text-slate-100">
                                <i :class="[card.subIcon, card.subtextClass]" class="text-[11px]"></i>
                                <span :class="['text-[11px] font-semibold tracking-wide', card.subtextClass]">
                                    {{ card.subtext }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- =========================== Card Section =========================== -->





            
        </div>
    </main>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../../../services/api";

const router = useRouter();

const loading = ref(false);
const errorMsg = ref("");

/* Dashboard State Variables */
const user = ref({});
const summary = ref({
    total_orders: 0,
    pending_orders: 0,
    confirmed_orders: 0,
    processing_orders: 0,
    picked_orders: 0,
    shipped_orders: 0,
    out_for_delivery_orders: 0,
    delivered_orders: 0,
    cancelled_orders: 0,
    failed_orders: 0,
    returned_orders: 0,
    pending_payments: 0,
    partial_payments: 0,
    paid_orders: 0,
    failed_payments: 0,
    refunded_orders: 0,
    cod_orders: 0,
    advance_orders: 0,
    total_sales: 0,
    total_revenue: 0,
    total_paid_amount: 0,
    total_due_amount: 0,
    total_shipping_charge: 0,
    total_discount: 0,
    total_coupon_discount: 0,
    total_tax: 0,
    total_points: 0,
});

const paymentSummary = ref({
    total_transactions: 0,

    pending: 0,
    processing: 0,
    success: 0,
    failed: 0,
    cancelled: 0,
    refunded: 0,

    payment: 0,
    refund: 0,
    adjustment: 0,

    online: 0,
    offline: 0,

    cod: 0,
    cash: 0,
    bank_transfer: 0,
    mobile_banking: 0,
    card: 0,
    paypal: 0,
    wallet: 0,

    bkash: 0,
    nagad: 0,
    rocket: 0,
    sslcommerz: 0,
    stripe: 0,
    manual: 0,
    bank: 0,
    cash_provider: 0,

    total_amount: 0,
    total_gateway_fee: 0,
    total_net_amount: 0,
});

/* Fetch API Data */
const fetchDashBoardData = async () => {
    try {
        loading.value = true;
        errorMsg.value = null;

        const response = await api.get('/dashboard'); 

        if (response.data?.success) {
            const data = response.data.data;
            user.value = data.user ?? {};
            summary.value = data.summary ?? {};
            paymentSummary.value = data.payment_summary;
        } else {
            errorMsg.value = response.data?.message || "Failed to fetch dashboard data.";
        }
    } catch (err) {
        console.error("Fetch Error:", err);
        errorMsg.value = err.response?.data?.message || "Something went wrong while connecting to the server.";
    } finally {
        loading.value = false;
    }
};

function WithdrawCreate() {
    router.push('/withdraw/create');
}

/* Computed Dynamic Order Dynamic Status Cards */
const statusCards = computed(() => [
    // Order Summary
    {
        key: "total_orders",
        title: "Total Orders",
        value: summary.value.total_orders,
        icon: "fa-solid fa-boxes-packing",
        iconWrap: "bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400",
        pill: "All",
        subIcon: "fa-solid fa-layer-group",
        subtext: "All orders",
        subtextClass: "text-indigo-500",
    },
    {
        key: "pending_orders",
        title: "Pending",
        value: summary.value.pending_orders,
        icon: "fa-solid fa-clock",
        iconWrap: "bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400",
        pill: "Order",
        subIcon: "fa-solid fa-hourglass-half",
        subtext: "Awaiting confirmation",
        subtextClass: "text-amber-500",
    },
    {
        key: "confirmed_orders",
        title: "Confirmed",
        value: summary.value.confirmed_orders,
        icon: "fa-solid fa-circle-check",
        iconWrap: "bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400",
        pill: "Order",
        subIcon: "fa-solid fa-check",
        subtext: "Confirmed orders",
        subtextClass: "text-blue-500",
    },
    {
        key: "processing_orders",
        title: "Processing",
        value: summary.value.processing_orders,
        icon: "fa-solid fa-gears",
        iconWrap: "bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400",
        pill: "Order",
        subIcon: "fa-solid fa-gear",
        subtext: "Being processed",
        subtextClass: "text-cyan-500",
    },
    {
        key: "picked_orders",
        title: "Picked",
        value: summary.value.picked_orders,
        icon: "fa-solid fa-box",
        iconWrap: "bg-violet-50 dark:bg-violet-500/10 text-violet-600 dark:text-violet-400",
        pill: "Order",
        subIcon: "fa-solid fa-box-open",
        subtext: "Packed & picked",
        subtextClass: "text-violet-500",
    },
    {
        key: "shipped_orders",
        title: "Shipped",
        value: summary.value.shipped_orders,
        icon: "fa-solid fa-truck-fast",
        iconWrap: "bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400",
        pill: "Order",
        subIcon: "fa-solid fa-route",
        subtext: "In transit",
        subtextClass: "text-purple-500",
    },
    {
        key: "out_for_delivery_orders",
        title: "Out for Delivery",
        value: summary.value.out_for_delivery_orders,
        icon: "fa-solid fa-truck",
        iconWrap: "bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400",
        pill: "Order",
        subIcon: "fa-solid fa-location-dot",
        subtext: "Courier delivering",
        subtextClass: "text-orange-500",
    },
    {
        key: "delivered_orders",
        title: "Delivered",
        value: summary.value.delivered_orders,
        icon: "fa-solid fa-house-circle-check",
        iconWrap: "bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
        pill: "Order",
        subIcon: "fa-solid fa-check-double",
        subtext: "Completed",
        subtextClass: "text-emerald-500",
    },
    {
        key: "cancelled_orders",
        title: "Cancelled",
        value: summary.value.cancelled_orders,
        icon: "fa-solid fa-ban",
        iconWrap: "bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400",
        pill: "Order",
        subIcon: "fa-solid fa-xmark",
        subtext: "Cancelled",
        subtextClass: "text-rose-500",
    },
    {
        key: "failed_orders",
        title: "Failed",
        value: summary.value.failed_orders,
        icon: "fa-solid fa-circle-xmark",
        iconWrap: "bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400",
        pill: "Order",
        subIcon: "fa-solid fa-triangle-exclamation",
        subtext: "Failed orders",
        subtextClass: "text-red-500",
    },
    {
        key: "returned_orders",
        title: "Returned",
        value: summary.value.returned_orders,
        icon: "fa-solid fa-rotate-left",
        iconWrap: "bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300",
        pill: "Order",
        subIcon: "fa-solid fa-arrow-rotate-left",
        subtext: "Returned items",
        subtextClass: "text-slate-500",
    },

    // Payment Status
    {
        key: "pending_payments",
        title: "Pending Payment",
        value: summary.value.pending_payments,
        icon: "fa-solid fa-wallet",
        iconWrap: "bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400",
        pill: "Payment",
        subIcon: "fa-solid fa-clock",
        subtext: "Awaiting payment",
        subtextClass: "text-yellow-500",
    },
    {
        key: "partial_payments",
        title: "Partial Payment",
        value: summary.value.partial_payments,
        icon: "fa-solid fa-coins",
        iconWrap: "bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400",
        pill: "Payment",
        subIcon: "fa-solid fa-coins",
        subtext: "Partially paid",
        subtextClass: "text-amber-500",
    },
    {
        key: "paid_orders",
        title: "Paid",
        value: summary.value.paid_orders,
        icon: "fa-solid fa-money-check-dollar",
        iconWrap: "bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400",
        pill: "Payment",
        subIcon: "fa-solid fa-check",
        subtext: "Payment completed",
        subtextClass: "text-green-500",
    },
    {
        key: "failed_payments",
        title: "Failed Payment",
        value: summary.value.failed_payments,
        icon: "fa-solid fa-circle-xmark",
        iconWrap: "bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400",
        pill: "Payment",
        subIcon: "fa-solid fa-triangle-exclamation",
        subtext: "Payment failed",
        subtextClass: "text-red-500",
    },
    {
        key: "refunded_orders",
        title: "Refunded",
        value: summary.value.refunded_orders,
        icon: "fa-solid fa-money-bill-transfer",
        iconWrap: "bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300",
        pill: "Payment",
        subIcon: "fa-solid fa-rotate-left",
        subtext: "Refund completed",
        subtextClass: "text-gray-500",
    },

    // Payment Method
    {
        key: "cod_orders",
        title: "Cash on Delivery",
        value: summary.value.cod_orders,
        icon: "fa-solid fa-money-bill-wave",
        iconWrap: "bg-lime-50 dark:bg-lime-500/10 text-lime-600 dark:text-lime-400",
        pill: "Method",
        subIcon: "fa-solid fa-sack-dollar",
        subtext: "COD orders",
        subtextClass: "text-lime-500",
    },
    {
        key: "advance_orders",
        title: "Advance Payment",
        value: summary.value.advance_orders,
        icon: "fa-solid fa-credit-card",
        iconWrap: "bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400",
        pill: "Method",
        subIcon: "fa-solid fa-globe",
        subtext: "Prepaid orders",
        subtextClass: "text-sky-500",
    },
]);

/* Count Formatter helper */
function formatCount(value) {
    const num = Number(value) || 0;

    if (num >= 1000000000) {
        return (num / 1000000000).toFixed(1).replace(/\.0$/, '') + 'B';
    }
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'k';
    }

    return num.toString();
}

const paymentCards = computed(() => [
    // === STATUS OVERVIEW ===
    {
        title: "Transactions",
        value: paymentSummary.value?.total_transactions ?? 0,
        icon: "fa-solid fa-money-check-dollar",
        pill: "Total",
        subtext: "Total attempted payments",
        iconWrap: "bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400",
        pillClass: "bg-indigo-50 text-indigo-700 border-indigo-200/50 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-transparent",
        hoverBorder: "hover:border-indigo-200 dark:hover:border-indigo-500/30",
    },
    {
        title: "Pending",
        value: paymentSummary.value?.pending ?? 0,
        icon: "fa-solid fa-clock",
        pill: "Status",
        subtext: "Awaiting confirmation",
        iconWrap: "bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400",
        pillClass: "bg-amber-50 text-amber-700 border-amber-200/50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-transparent",
        hoverBorder: "hover:border-amber-200 dark:hover:border-amber-500/30",
    },
    {
        title: "Processing",
        value: paymentSummary.value?.processing ?? 0,
        icon: "fa-solid fa-spinner",
        pill: "Status",
        subtext: "In-flight transactions",
        iconWrap: "bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400",
        pillClass: "bg-blue-50 text-blue-700 border-blue-200/50 dark:bg-blue-500/10 dark:text-blue-400 dark:border-transparent",
        hoverBorder: "hover:border-blue-200 dark:hover:border-blue-500/30",
    },
    {
        title: "Success",
        value: paymentSummary.value?.success ?? 0,
        icon: "fa-solid fa-circle-check",
        pill: "Completed",
        subtext: "Settled payments",
        iconWrap: "bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
        pillClass: "bg-emerald-50 text-emerald-700 border-emerald-200/50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-transparent",
        hoverBorder: "hover:border-emerald-200 dark:hover:border-emerald-500/30",
    },
    {
        title: "Failed",
        value: paymentSummary.value?.failed ?? 0,
        icon: "fa-solid fa-circle-xmark",
        pill: "Alert",
        subtext: "Declined transactions",
        iconWrap: "bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400",
        pillClass: "bg-rose-50 text-rose-700 border-rose-200/50 dark:bg-rose-500/10 dark:text-rose-400 dark:border-transparent",
        hoverBorder: "hover:border-rose-200 dark:hover:border-rose-500/30",
    },
    {
        title: "Refunded",
        value: paymentSummary.value?.refunded ?? 0,
        icon: "fa-solid fa-rotate-left",
        pill: "Return",
        subtext: "Returned to customer",
        iconWrap: "bg-teal-50 dark:bg-teal-500/10 text-teal-600 dark:text-teal-400",
        pillClass: "bg-teal-50 text-teal-700 border-teal-200/50 dark:bg-teal-500/10 dark:text-teal-400 dark:border-transparent",
        hoverBorder: "hover:border-teal-200 dark:hover:border-teal-500/30",
    },

    // === CHANNELS ===
    {
        title: "Online",
        value: paymentSummary.value?.online ?? 0,
        icon: "fa-solid fa-globe",
        pill: "Channel",
        subtext: "Automated gateways",
        iconWrap: "bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400",
        pillClass: "bg-sky-50 text-sky-700 border-sky-200/50 dark:bg-sky-500/10 dark:text-sky-400 dark:border-transparent",
        hoverBorder: "hover:border-sky-200 dark:hover:border-sky-500/30",
    },
    {
        title: "Offline",
        value: paymentSummary.value?.offline ?? 0,
        icon: "fa-solid fa-building",
        pill: "Channel",
        subtext: "Manual / Cash collection",
        iconWrap: "bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300",
        pillClass: "bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-transparent",
        hoverBorder: "hover:border-slate-300 dark:hover:border-slate-600",
    },

    // === GATEWAYS & MFS ===
    {
        title: "bKash",
        value: paymentSummary.value?.bkash ?? 0,
        icon: "fa-solid fa-mobile-screen",
        pill: "MFS",
        subtext: "Mobile wallet",
        iconWrap: "bg-pink-50 dark:bg-pink-500/10 text-pink-600 dark:text-pink-400",
        pillClass: "bg-pink-50 text-pink-700 border-pink-200/50 dark:bg-pink-500/10 dark:text-pink-400 dark:border-transparent",
        hoverBorder: "hover:border-pink-200 dark:hover:border-pink-500/30",
    },
    {
        title: "Nagad",
        value: paymentSummary.value?.nagad ?? 0,
        icon: "fa-solid fa-wallet",
        pill: "MFS",
        subtext: "Mobile wallet",
        iconWrap: "bg-orange-50 dark:bg-orange-500/10 text-orange-600 dark:text-orange-400",
        pillClass: "bg-orange-50 text-orange-700 border-orange-200/50 dark:bg-orange-500/10 dark:text-orange-400 dark:border-transparent",
        hoverBorder: "hover:border-orange-200 dark:hover:border-orange-500/30",
    },
    {
        title: "Rocket",
        value: paymentSummary.value?.rocket ?? 0,
        icon: "fa-solid fa-rocket",
        pill: "MFS",
        subtext: "Mobile wallet",
        iconWrap: "bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400",
        pillClass: "bg-purple-50 text-purple-700 border-purple-200/50 dark:bg-purple-500/10 dark:text-purple-400 dark:border-transparent",
        hoverBorder: "hover:border-purple-200 dark:hover:border-purple-500/30",
    },
    {
        title: "SSLCommerz",
        value: paymentSummary.value?.sslcommerz ?? 0,
        icon: "fa-solid fa-credit-card",
        pill: "Gateway",
        subtext: "Card / Multi-gateway",
        iconWrap: "bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400",
        pillClass: "bg-cyan-50 text-cyan-700 border-cyan-200/50 dark:bg-cyan-500/10 dark:text-cyan-400 dark:border-transparent",
        hoverBorder: "hover:border-cyan-200 dark:hover:border-cyan-500/30",
    },

    // === FINANCIAL TOTALS ===
    {
        title: "Total Amount",
        value: paymentSummary.value?.total_amount ?? 0,
        icon: "fa-solid fa-sack-dollar",
        pill: "Gross",
        subtext: "Total collected amount",
        iconWrap: "bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400",
        pillClass: "bg-emerald-50 text-emerald-700 border-emerald-200/50 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-transparent",
        hoverBorder: "hover:border-emerald-200 dark:hover:border-emerald-500/30",
    },
    {
        title: "Total Net Amount",
        value: paymentSummary.value?.total_net_amount ?? 0,
        icon: "fa-solid fa-vault",
        pill: "Net Revenue",
        subtext: "Net earnings after gateway fee",
        iconWrap: "bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400",
        pillClass: "bg-blue-50 text-blue-700 border-blue-200/50 dark:bg-blue-500/10 dark:text-blue-400 dark:border-transparent",
        hoverBorder: "hover:border-blue-200 dark:hover:border-blue-500/30",
    },
    {
        title: "Total Gateway Fee",
        value: paymentSummary.value?.total_gateway_fee ?? 0,
        icon: "fa-solid fa-receipt",
        pill: "Deduction",
        subtext: "Total charges paid to gateways",
        iconWrap: "bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400",
        pillClass: "bg-amber-50 text-amber-700 border-amber-200/50 dark:bg-amber-500/10 dark:text-amber-400 dark:border-transparent",
        hoverBorder: "hover:border-amber-200 dark:hover:border-amber-500/30",
    },
]);

onMounted(() => {
    fetchDashBoardData();
});
</script>

<style scoped>
</style>