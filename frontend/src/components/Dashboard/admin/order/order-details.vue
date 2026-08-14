<template>
    <div class="min-h-screen bg-white dark:bg-slate-950 transition-colors duration-200">
        <HeaderSection
            :is-dark="isDark"
            @toggle-dark="toggleDarkMode"
            @toggle-menu="toggleMenu"
        />

        <div class="flex  min-h-[calc(100vh-56px)]">
            <Navbar
                :mobile-menu="mobileMenu"
                @close="mobileMenu = false" />

            <Message
                :successMsg="successMsg"
                :errorMsg="errorMsg"
                @update:successMsg="successMsg = $event"
                @update:errorMsg="errorMsg = $event"
            />

            <!-- Content -->
            <div class="flex-1 min-w-0">
                <main class="flex-1 min-h-screen min-w-0 bg-gray-50 dark:bg-[#0C1326] px-4 sm:px-6 lg:px-8 py-6 transition-colors duration-300">

                    <!-- Loading skeleton -->
                    <div v-if="loading && !order" class="space-y-6 animate-pulse">
                        <div class="flex items-center justify-between pb-5 border-b border-slate-200 dark:border-slate-800/60">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-slate-200 dark:bg-slate-800"></div>
                                <div class="space-y-2">
                                    <div class="h-5 w-40 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                    <div class="h-3 w-56 bg-slate-200 dark:bg-slate-800 rounded"></div>
                                </div>
                            </div>
                            <div class="flex gap-3">
                                <div class="h-9 w-32 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                                <div class="h-9 w-32 bg-slate-200 dark:bg-slate-800 rounded-lg"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div v-for="n in 4" :key="n" class="h-24 bg-slate-200 dark:bg-slate-800 rounded-xl"></div>
                                </div>
                                <div class="h-32 bg-slate-200 dark:bg-slate-800 rounded-2xl"></div>
                                <div class="h-72 bg-slate-200 dark:bg-slate-800 rounded-2xl"></div>
                                <div class="h-48 bg-slate-200 dark:bg-slate-800 rounded-2xl"></div>
                            </div>
                            <div class="h-96 bg-slate-200 dark:bg-slate-800 rounded-2xl"></div>
                        </div>
                    </div>

                    <!-- Not found -->
                    <div v-else-if="!order" class="flex flex-col items-center text-center py-24">
                        <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center mb-4">
                            <i class="fa-solid fa-circle-exclamation text-2xl text-slate-400 dark:text-slate-500"></i>
                        </div>
                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Order not found</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-xs leading-relaxed">
                            {{ errorMsg || 'This order may not exist or may have been removed.' }}
                        </p>
                        <button @click="$router.back()" class="mt-5 px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition shadow-sm">
                            Go back
                        </button>
                    </div>

                    <div v-else>

                        <!-- Page header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-200 dark:border-slate-800/60">
                            <div class="flex items-center gap-4">
                                <button @click="$router.back()" class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm shrink-0">
                                    <i class="fa-solid fa-arrow-left-long text-slate-600 dark:text-slate-400"></i>
                                </button>

                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                            Order <span class="text-green-600 dark:text-orange-400">#0000000000</span>
                                        </h1>

                                        <span
                                            class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-mono text-xs font-semibold px-2 py-0.5 rounded-full border border-amber-200/60 dark:border-amber-900/50 uppercase tracking-wider"
                                            title="Coupon Applied" >
                                            <i class="fa-solid fa-tags text-amber-500"></i>
                                            Coupon
                                        </span>
                                    </div>

                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-regular fa-calendar text-slate-400 dark:text-slate-500"></i>
                                        Placed on {{ formatDate(order.order_date) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button
                                    class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm">
                                    <i class="fa-solid fa-download mr-1.5"></i>
                                    Download Invoice
                                </button>
                                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-md shadow-indigo-500/20 transition">
                                    <i class="fa-solid fa-print mr-1.5"></i>Print Details
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <div class="lg:col-span-2 space-y-6">

                                <!-- Metric cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">

                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                                        <div class="flex items-center gap-2 mb-3">
                                            <i class="fa-regular fa-calendar text-slate-400 text-sm"></i>
                                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Payment Date</p>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            {{ order.paid_at ? formatDate(order.paid_at) : 'Waiting for Payment' }}
                                        </p>
                                    </div>

                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                                        <div class="flex items-center gap-2 mb-3">
                                            <i class="fa-solid fa-wallet text-slate-400 text-sm"></i>
                                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Amount</p>
                                        </div>
                                        <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white">{{ order.currency }} ৳ {{ Number(order.payable_amount).toLocaleString() }}</p>
                                    </div>

                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all group flex flex-col justify-between">
                                        <div class="flex justify-between items-center mb-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-truck-fast text-slate-400 text-sm"></i>
                                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Order Status</p>
                                            </div>
                                            <i class="fa-solid fa-pencil h-4 w-4 text-slate-400 opacity-0 group-hover:opacity-100 transition"></i>
                                        </div>
                                        <span class="px-3 py-1 rounded-lg text-[11px] font-bold uppercase inline-flex items-center gap-2 border border-transparent dark:border-current/10 w-fit">
                                            <span class="h-2 w-2 rounded-full"></span>
                                            {{ order.status }}
                                        </span>
                                    </div>

                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col justify-between">
                                        <div class="flex items-center gap-2 mb-3">
                                            <i class="fa-regular fa-star text-slate-400 text-sm"></i>
                                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Point</p>
                                        </div>
                                        <p class="text-2xl font-bold font-mono text-slate-900 dark:text-white">{{ Number(order.point).toLocaleString() }}</p>
                                    </div>

                                </div>

                                <!-- Transaction details -->
                                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

                                    <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                        <div>
                                            <h3 class="font-bold text-base text-slate-900 dark:text-white">Transaction Details</h3>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Order receipt</p>
                                        </div>
                                        <span :class="[
                                            'inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-full',
                                            statusStyle(order.payment_status).badge
                                        ]">
                                            <i :class="statusStyle(order.payment_status).icon"></i>
                                            {{ order.payment_status }}
                                        </span>
                                    </div>

                                    <div class="px-6 py-4 space-y-3">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-slate-500 dark:text-slate-400">Registration Number</span>
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ order.reg }}</span>
                                        </div>
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-slate-500 dark:text-slate-400">Currency</span>
                                            <span class="text-sm font-semibold text-slate-900 dark:text-white">{{ order.currency }} — Bangladeshi Taka</span>
                                        </div>
                                    </div>

                                    <div class="relative px-6">
                                        <div class="border-t border-dashed border-slate-200 dark:border-slate-700"></div>
                                        <div class="absolute -left-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-gray-50 dark:bg-[#0C1326]"></div>
                                        <div class="absolute -right-3 top-1/2 -translate-y-1/2 w-6 h-6 rounded-full bg-gray-50 dark:bg-[#0C1326]"></div>
                                    </div>

                                    <div class="px-6 pt-5 pb-6">
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-3">Amount Breakdown</h4>

                                        <div class="space-y-2 text-sm">
                                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                                <span>Subtotal</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">{{ order.currency }} ৳ {{ Number(order.amount).toLocaleString() }}</span>
                                            </div>

                                            <div v-if="Number(order.discount) > 0" class="flex justify-between text-slate-500 dark:text-slate-400">
                                                <span>Discount</span>
                                                <span class="font-medium text-emerald-600 dark:text-emerald-400">− {{ order.currency }} ৳ {{ Number(order.discount).toLocaleString() }}</span>
                                            </div>

                                            <div v-if="Number(order.coupon_discount) > 0" class="flex justify-between text-slate-500 dark:text-slate-400">
                                                <span>Coupon Discount</span>
                                                <span class="font-medium text-emerald-600 dark:text-emerald-400">− {{ order.currency }} ৳ {{ Number(order.coupon_discount).toLocaleString() }}</span>
                                            </div>

                                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                                <span>Shipping Charge</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">+ {{ order.currency }} ৳ {{ Number(order.shipping_charge).toLocaleString() }}</span>
                                            </div>

                                            <div class="flex justify-between text-slate-500 dark:text-slate-400">
                                                <span>Tax</span>
                                                <span class="font-medium text-slate-700 dark:text-slate-300">+ {{ order.currency }} ৳ {{ Number(order.tax).toLocaleString() }}</span>
                                            </div>
                                        </div>

                                        <div class="flex justify-between items-baseline border-t border-slate-100 dark:border-slate-800 mt-4 pt-4">
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">Total Payable</span>
                                            <span class="text-xl font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ order.currency }} ৳ {{ Number(order.payable_amount).toLocaleString() }}</span>
                                        </div>

                                        <!-- Paid / Due summary — derived from the payments relation -->
                                        <div class="mt-4 pt-4 border-t border-dashed border-slate-200 dark:border-slate-700 space-y-2">
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-slate-500 dark:text-slate-400">
                                                    <i class="fa-solid fa-circle-check text-emerald-500 mr-1.5"></i>Paid
                                                </span>
                                                <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                                    {{ order.currency }} ৳ {{ totalPaid.toLocaleString() }}
                                                </span>
                                            </div>
                                            <div class="flex justify-between items-center text-sm">
                                                <span class="text-slate-500 dark:text-slate-400">
                                                    <i class="fa-solid fa-hourglass-half mr-1.5" :class="dueAmount > 0 ? 'text-red-500' : 'text-slate-400'"></i>Due
                                                </span>
                                                <span class="font-mono font-semibold" :class="dueAmount > 0 ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'">
                                                    {{ order.currency }} ৳ {{ dueAmount.toLocaleString() }}
                                                </span>
                                            </div>
                                            <span v-if="dueAmount > 0"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-amber-700 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2.5 py-1 rounded-full mt-1">
                                                <i class="fa-solid fa-triangle-exclamation"></i> Partially paid
                                            </span>
                                        </div>

                                        <details v-if="order.user_agent" class="mt-3 pt-3 border-t border-dashed border-slate-200 dark:border-slate-700 text-xs group">
                                            <summary class="text-slate-400 dark:text-slate-500 cursor-pointer select-none list-none flex items-center justify-between">
                                                <span><i class="fa-solid fa-circle-info mr-1"></i>Device details</span>
                                                <i class="fa-solid fa-chevron-down text-[10px] transition-transform group-open:rotate-180"></i>
                                            </summary>
                                            <div class="mt-2 grid grid-cols-2 gap-y-1.5">
                                                <span class="text-slate-400 dark:text-slate-500">Browser</span>
                                                <span class="text-right font-medium text-slate-700 dark:text-slate-300">{{ parseUserAgent(order.user_agent).browser }}</span>
                                                <span class="text-slate-400 dark:text-slate-500">OS</span>
                                                <span class="text-right font-medium text-slate-700 dark:text-slate-300">{{ parseUserAgent(order.user_agent).os }}</span>
                                                <span class="text-slate-400 dark:text-slate-500">Device</span>
                                                <span class="text-right font-medium text-slate-700 dark:text-slate-300">{{ parseUserAgent(order.user_agent).device }}</span>
                                            </div>
                                        </details>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                                    <!-- ============================= -->
                                    <!-- Payment History               -->
                                    <!-- ============================= -->
                                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">

                                        <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100 dark:border-slate-800">
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Payment History</h3>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Every attempt recorded for this order</p>
                                            </div>
                                            <span v-if="payments && payments.length"
                                                class="text-xs font-semibold text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-800 px-2.5 py-1 rounded-full shrink-0">
                                                {{ payments.length }} {{ payments.length === 1 ? 'attempt' : 'attempts' }}
                                            </span>
                                        </div>

                                        <div v-if="payments && payments.length" class="divide-y divide-slate-100 dark:divide-slate-800">
                                            <div v-for="payment in payments" :key="payment.id"
                                                class="px-6 py-5 transition-opacity"
                                                :class="{ 'opacity-60': ['Failed','Cancelled'].includes(payment.status) }">

                                                <div class="flex items-start gap-3.5">
                                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                                                        :class="getPaymentMethod(payment.payment_method).iconBg">
                                                        <i :class="[getPaymentMethod(payment.payment_method).icon, getPaymentMethod(payment.payment_method).iconColor]" class="text-base"></i>
                                                    </div>

                                                    <div class="min-w-0 flex-1">
                                                        <!-- Method / status row -->
                                                        <div class="flex items-start justify-between gap-3">
                                                            <div class="min-w-0">
                                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-200 truncate">
                                                                    {{ getPaymentMethod(payment.payment_method).label }}
                                                                    <span v-if="payment.provider" class="font-normal text-slate-400">via {{ payment.provider }}</span>
                                                                    <span v-if="payment.payment_type === 'Refund'"
                                                                        class="ml-1.5 inline-flex items-center text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400">
                                                                        Refund
                                                                    </span>
                                                                    <span v-else-if="payment.payment_type === 'Adjustment'"
                                                                        class="ml-1.5 inline-flex items-center text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-sky-50 dark:bg-sky-500/10 text-sky-600 dark:text-sky-400">
                                                                        Adjustment
                                                                    </span>
                                                                </p>
                                                                <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                                    <i class="fa-regular fa-clock mr-1"></i>{{ payment.paid_at ? formatDate(payment.paid_at) : formatDate(payment.created_at) }}
                                                                    <span v-if="payment.receipt_no" class="font-mono ml-1.5">· #{{ payment.receipt_no }}</span>
                                                                </p>
                                                            </div>

                                                            <div class="text-right shrink-0">
                                                                <span :class="getPaymentStatus(payment.status).badge" class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                                    <span class="w-1.5 h-1.5 rounded-full" :class="getPaymentStatus(payment.status).dot"></span>
                                                                    {{ payment.status }}
                                                                </span>
                                                                <p class="text-sm font-mono font-bold mt-1.5"
                                                                    :class="payment.payment_type === 'Refund' ? 'text-purple-600 dark:text-purple-400' : 'text-slate-900 dark:text-white'">
                                                                    {{ payment.payment_type === 'Refund' ? '−' : '' }}{{ payment.currency }} ৳ {{ Number(payment.amount).toLocaleString() }}
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <!-- Failure reason -->
                                                        <div v-if="payment.status === 'Failed' && payment.failure_reason"
                                                            class="flex items-start gap-1.5 text-xs text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-500/10 rounded-lg px-3 py-2 mt-3">
                                                            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>
                                                            <span>{{ payment.failure_reason }}</span>
                                                        </div>

                                                        <!-- Always-visible details panel — replaces the old collapsed <details> -->
                                                        <div v-if="payment.transaction_id || payment.sender_mobile || payment.account_holder_name || payment.bank_name || payment.account_number || payment.channel || Number(payment.gateway_fee) > 0 || Number(payment.net_amount) > 0 || payment.receiver?.name || payment.paid_at || payment.user_agent"
                                                            class="mt-3 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-800/30 px-4 py-3.5">
                                                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-4 gap-y-3">

                                                                <div v-if="payment.transaction_id">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Transaction ID</p>
                                                                    <p class="text-xs font-mono font-medium text-slate-700 dark:text-slate-300 truncate">{{ payment.transaction_id }}</p>
                                                                </div>
                                                                <div v-if="payment.sender_mobile">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Sender mobile</p>
                                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ payment.sender_mobile }}</p>
                                                                </div>
                                                                <div v-if="payment.account_holder_name">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Acc. holder</p>
                                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ payment.account_holder_name }}</p>
                                                                </div>
                                                                <div v-if="payment.bank_name">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Bank</p>
                                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ payment.bank_name }}</p>
                                                                </div>
                                                                <div v-if="payment.account_number">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Account</p>
                                                                    <p class="text-xs font-mono font-medium text-slate-700 dark:text-slate-300">{{ payment.account_number }}</p>
                                                                </div>
                                                                <div v-if="payment.channel">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Channel</p>
                                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ payment.channel }}</p>
                                                                </div>
                                                                <div v-if="Number(payment.gateway_fee) > 0">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Gateway fee</p>
                                                                    <p class="text-xs font-mono font-medium text-slate-700 dark:text-slate-300">{{ payment.currency }} ৳ {{ Number(payment.gateway_fee).toLocaleString() }}</p>
                                                                </div>
                                                                <div v-if="Number(payment.net_amount) > 0">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Net amount</p>
                                                                    <p class="text-xs font-mono font-medium text-slate-700 dark:text-slate-300">{{ payment.currency }} ৳ {{ Number(payment.net_amount).toLocaleString() }}</p>
                                                                </div>
                                                                <div v-if="payment.receiver?.name">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Received by</p>
                                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ payment.receiver.name }}</p>
                                                                </div>
                                                                <div v-if="payment.paid_at">
                                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Paid at</p>
                                                                    <p class="text-xs font-medium text-slate-700 dark:text-slate-300">
                                                                        {{ new Date(payment.paid_at).toLocaleString('en-BD', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true }) }}
                                                                    </p>
                                                                </div>
                                                                <template v-if="payment.user_agent">
                                                                    <div>
                                                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Browser</p>
                                                                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ parseUserAgent(payment.user_agent).browser }}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">OS</p>
                                                                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ parseUserAgent(payment.user_agent).os }}</p>
                                                                    </div>
                                                                    <div>
                                                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500">Device</p>
                                                                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300">{{ parseUserAgent(payment.user_agent).device }}</p>
                                                                    </div>
                                                                </template>
                                                            </div>
                                                        </div>

                                                        <p v-if="payment.remarks" class="text-xs text-slate-500 dark:text-slate-400 mt-3 italic">
                                                            "{{ payment.remarks }}"
                                                        </p>

                                                        <!-- Verify / verified row -->
                                                        <div v-if="(payment.status === 'Pending' && payment.provider === 'manual') || payment.verified_at"
                                                            class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                                                            <p v-if="payment.verified_at" class="text-[11px] text-slate-400 dark:text-slate-500 flex items-center gap-1">
                                                                <i class="fa-solid fa-circle-check text-emerald-500"></i>
                                                                Verified {{ formatDate(payment.verified_at) }} by {{ payment.verifier?.name || '—' }}
                                                            </p>
                                                            <span v-else></span>
                                                            <button v-if="payment.status === 'Pending' && payment.provider === 'manual'" @click="verifyPayment(payment)"
                                                                class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                                <i class="fa-solid fa-shield-check mr-1"></i>Verify payment
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-else class="flex flex-col items-center text-center py-12 px-6">
                                            <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center mb-4">
                                                <i class="fa-solid fa-receipt text-2xl text-slate-400 dark:text-slate-500"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No payment attempts yet</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-xs leading-relaxed">
                                                This order is awaiting payment. Once a payment is made, it will appear here.
                                            </p>
                                            <button @click="openPaymentModal" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/20 active:scale-95 transition-all">
                                                <i class="fa-solid fa-credit-card text-xs"></i>
                                                Make payment
                                            </button>
                                        </div>
                                    </div>

                                    <!-- ============================= -->
                                    <!-- Delivery charge payment — unchanged, kept as-is -->
                                    <!-- ============================= -->
                                    <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">

                                        <div class="flex items-center justify-between mb-5">
                                            <div>
                                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Delivery Charge Payment</h3>
                                                <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Courier / shipping fee settlement</p>
                                            </div>
                                        </div>

                                        <div v-if="deliveryCharge">
                                            <!-- Stat strip: amount / status / date as three peers -->
                                            <div class="grid grid-cols-3 divide-x divide-slate-100 dark:divide-slate-800 rounded-xl border border-slate-100 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-800/30 mb-5">
                                                <div class="px-4 py-3.5 text-center">
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Amount</p>
                                                    <p class="text-base font-mono font-bold text-slate-900 dark:text-white">{{ deliveryCharge.currency }} ৳ {{ Number(deliveryCharge.amount).toLocaleString() }}</p>
                                                </div>
                                                <div class="px-4 py-3.5 text-center relative">
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Status</p>
                                                    <button @click="statusDropdownOpen = !statusDropdownOpen"
                                                        :class="getPaymentStatus(deliveryCharge.payment_status).badge"
                                                        class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap capitalize hover:opacity-80 transition">
                                                        <span class="w-1.5 h-1.5 rounded-full" :class="getPaymentStatus(deliveryCharge.payment_status).dot"></span>
                                                        {{ deliveryCharge.payment_status }}
                                                        <i class="fa-solid fa-chevron-down text-[9px] transition-transform" :class="{ 'rotate-180': statusDropdownOpen }"></i>
                                                    </button>

                                                    <div v-if="statusDropdownOpen"
                                                        class="absolute z-10 left-1/2 -translate-x-1/2 mt-1.5 w-44 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden text-left">
                                                        <button v-for="option in statusOptions" :key="option.value" @click="updateDeliveryStatus(option.value)"
                                                            class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-800 transition"
                                                            :class="option.value === deliveryCharge.payment_status ? 'bg-slate-50 dark:bg-slate-800' : ''">
                                                            <span class="flex items-center gap-2">
                                                                <span class="w-1.5 h-1.5 rounded-full" :class="getPaymentStatus(option.value).dot"></span>
                                                                <span class="font-medium text-slate-700 dark:text-slate-300 capitalize">{{ option.value }}</span>
                                                            </span>
                                                            <i v-if="option.value === deliveryCharge.payment_status" class="fa-solid fa-check text-[11px] text-indigo-500"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="px-4 py-3.5 text-center">
                                                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 dark:text-slate-500 mb-1">Method</p>
                                                    <p class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center justify-center gap-1.5">
                                                        <i :class="[getPaymentMethod(deliveryCharge.payment_method).icon, getPaymentMethod(deliveryCharge.payment_method).iconColor]"></i>
                                                        {{ getPaymentMethod(deliveryCharge.payment_method).label }}
                                                    </p>
                                                </div>
                                            </div>

                                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mb-4">
                                                <i class="fa-regular fa-clock mr-1"></i>{{ formatDate(deliveryCharge.payment_date) }}
                                            </p>

                                            <div class="space-y-2 text-xs">
                                                <div v-if="deliveryCharge.transaction_id" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Transaction ID</span>
                                                    <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.transaction_id }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.reference_no" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Reference no.</span>
                                                    <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.reference_no }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.bank_name" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Provider</span>
                                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.bank_name }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.branch_name" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Branch</span>
                                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.branch_name }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.account_number" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Account</span>
                                                    <span class="font-mono font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.account_number }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.account_holder_name" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Account holder</span>
                                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.account_holder_name }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.mobile_number" class="flex items-center justify-between">
                                                    <span class="text-slate-400 dark:text-slate-500">Mobile</span>
                                                    <span class="font-medium text-slate-700 dark:text-slate-300">{{ deliveryCharge.mobile_number }}</span>
                                                </div>
                                                <div v-if="deliveryCharge.paid_by" class="flex items-center justify-between pt-1">
                                                    <span class="text-slate-400 dark:text-slate-500">Paid by</span>
                                                    <span class="inline-flex items-center gap-1.5 font-medium text-slate-700 dark:text-slate-300">
                                                        <span class="w-5 h-5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 flex items-center justify-center text-[9px] font-bold text-indigo-600 dark:text-indigo-400 shrink-0">
                                                            {{ deliveryCharge.paid_by.name?.charAt(0).toUpperCase() }}
                                                        </span>
                                                        {{ deliveryCharge.paid_by.name }}
                                                    </span>
                                                </div>
                                                <div v-if="deliveryCharge.attachment" class="flex items-center justify-between pt-1">
                                                    <span class="text-slate-400 dark:text-slate-500">Attachment</span>
                                                    <a :href="deliveryCharge.attachment" target="_blank" class="inline-flex items-center gap-1.5 font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                        <i class="fa-solid fa-paperclip"></i>View file
                                                    </a>
                                                </div>
                                            </div>

                                            <p v-if="deliveryCharge.notes" class="text-xs text-slate-500 dark:text-slate-400 italic mt-4 pt-4 border-t border-dashed border-slate-200 dark:border-slate-700">
                                                "{{ deliveryCharge.notes }}"
                                            </p>
                                        </div>

                                        <div v-else class="flex flex-col items-center text-center py-10 px-4">
                                            <div class="w-14 h-14 rounded-2xl bg-slate-50 dark:bg-slate-800/60 flex items-center justify-center mb-4">
                                                <i class="fa-solid fa-truck text-2xl text-slate-400 dark:text-slate-500"></i>
                                            </div>
                                            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">No delivery charge payment yet</p>
                                            <p class="text-xs text-slate-400 dark:text-slate-500 mt-1 max-w-xs leading-relaxed">
                                                Delivery charge payment info will appear here once submitted.
                                            </p>
                                        </div>
                                    </div>

                                </div>

                                <!-- Order items -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <i class="fa-solid fa-bag-shopping text-indigo-500"></i>
                                            Order Items ({{ cartItems.length }})
                                        </h3>
                                    </div>

                                    <div v-for="item in cartItems" :key="item.id"
                                        class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300">

                                        <div class="flex flex-col sm:flex-row items-center gap-6">

                                            <div class="relative h-24 w-24 flex-shrink-0 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">

                                                <img :src="getProductImage(item)" :alt="item.product?.name || 'Product Image'"
                                                    class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500"
                                                    @error="(e) => e.target.src = defaultProductImage" />

                                                <div class="absolute top-1 right-1 bg-slate-900/80 text-white text-[10px] font-bold px-2 py-0.5 rounded-lg backdrop-blur-sm">
                                                    x{{ item.quantity }}
                                                </div>
                                            </div>

                                            <div class="flex-1 flex flex-col md:flex-row justify-between w-full gap-4">
                                                <div>
                                                    <h4 @click="ProductDetails(item)" class="text-base font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 group-hover:underline group-hover:cursor-pointer transition-colors duration-300">
                                                    {{ item.product?.name }}
                                                    </h4>

                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                    <span v-if="item.variant?.color"
                                                            class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                                        <span class="w-2 h-2 rounded-full mr-2" :style="{ backgroundColor: item.variant.color.toLowerCase() }"></span>
                                                        {{ item.variant.color }}
                                                    </span>

                                                    <span v-if="item.variant?.size"
                                                            class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                                                        Size: {{ item.variant.size }}
                                                    </span>

                                                    <span v-if="!item.variant"
                                                            class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-50 dark:bg-slate-800 text-slate-400 border border-slate-100 dark:border-slate-700">
                                                        Standard
                                                    </span>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col md:items-end justify-center min-w-[120px]">
                                                    <p class="flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-[0.15em] mb-0.5">
                                                        <i class="fa-solid fa-arrows-to-circle"></i>
                                                        Points Earned
                                                    </p>
                                                    <div class="flex items-baseline gap-1">
                                                        <span class="text-2xl font-bold text-slate-900 dark:text-white tracking-tight leading-none">
                                                            {{ (Number(item.point) * item.quantity).toLocaleString() }}
                                                        </span>
                                                        <span class="text-[10px] font-bold text-indigo-500/80 dark:text-indigo-400/80 uppercase">pts</span>
                                                    </div>
                                                </div>

                                                <div class="flex flex-col md:items-end justify-center min-w-[120px]">
                                                    <div class="text-xs text-slate-400 mb-1 flex items-center gap-1">
                                                    <span>Unit: ৳{{ Number(item.price).toLocaleString() }}</span>
                                                    <span class="text-slate-300 dark:text-slate-700">|</span>
                                                    <span>Qty: {{ item.quantity }}</span>
                                                    </div>

                                                    <div class="flex flex-col md:items-end">
                                                    <span class="text-xs font-bold text-indigo-500/80 dark:text-indigo-400 uppercase tracking-tighter">Subtotal</span>
                                                    <div class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                                        <span class="text-sm font-normal mr-0.5">৳</span>{{ (Number(item.price) * item.quantity).toLocaleString() }}
                                                    </div>
                                                    </div>

                                                    <div v-if="item.discount > 0" class="mt-1">
                                                    <span class="inline-flex items-center text-[10px] font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 rounded-full border border-emerald-100 dark:border-emerald-500/20">
                                                        Saved ৳{{ (item.discount * item.quantity).toLocaleString() }}
                                                    </span>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div class="sticky top-16">
                                    <div class=" bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                                        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                                            <h3 class="font-bold text-slate-900 dark:text-white">Customer Details</h3>
                                        </div>
                                        <div class="p-6 text-center">
                                            <div class="relative inline-block mb-4">
                                                <img v-if="order.user?.photo" :src="photoUrl" alt="User photo" class="h-16 w-16 rounded-2xl object-cover ring-2 ring-slate-200 dark:ring-white/10"/>
                                                <div v-else class="h-20 w-20 rounded-2xl bg-gradient-to-tr from-indigo-600 to-purple-500 flex items-center justify-center text-white text-2xl font-bold shadow-lg shadow-indigo-500/30">
                                                    {{ order.user?.name?.substring(0, 2).toUpperCase() || '—' }}
                                                </div>
                                                <div class="absolute -bottom-2 -right-2 bg-green-500 border-4 border-white dark:border-slate-900 h-6 w-6 rounded-full" title="Active User"></div>
                                            </div>

                                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ order.user?.name }}</h4>
                                            <p class="text-sm text-slate-500 mb-6">UID#{{ order.user?.user_id }}</p>

                                            <div class="text-left space-y-3 border-t border-slate-100 dark:border-slate-800 py-4">
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-envelope"></i>
                                                    <span>{{ order.user?.email }}</span>
                                                </div>
                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-location-dot mt-0.5"></i>
                                                    <span class="leading-relaxed">
                                                        <span v-if="order.user?.present_address">{{ order.user.present_address }}</span>
                                                        <span v-else-if="order.user?.permanent_address">{{ order.user.permanent_address }}</span>
                                                        <span v-else class="text-slate-400">No address on file</span>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="rounded-2xl border border-slate-200/70 dark:border-slate-800 bg-white dark:bg-slate-900 divide-y divide-slate-100 dark:divide-slate-800 shadow-sm">

                                                <!-- Contact Information -->
                                                <div class="p-4 sm:p-5 space-y-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200/80 dark:ring-slate-700 text-slate-500 dark:text-slate-400 shrink-0">
                                                            <i class="fa-regular fa-id-card text-[13px]"></i>
                                                        </span>
                                                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                                            Customer Details
                                                        </h3>
                                                    </div>
 
                                                    <div class="rounded-xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 divide-y divide-dashed divide-slate-200 dark:divide-slate-700">
                                                        <div class="flex items-center gap-3 p-3">
                                                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-800 ring-1 ring-slate-200/80 dark:ring-slate-700 text-slate-500 dark:text-slate-400 shrink-0">
                                                                <i class="fa-regular fa-user text-[13px]"></i>
                                                            </span>
                                                            <div class="min-w-0">
                                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-semibold tracking-wider">Full name</p>
                                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ order.contact_name }}</p>
                                                            </div>
                                                        </div>
 
                                                        <div class="flex items-center gap-3 p-3">
                                                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-800 ring-1 ring-slate-200/80 dark:ring-slate-700 text-slate-500 dark:text-slate-400 shrink-0">
                                                                <i class="fa-solid fa-phone text-[13px]"></i>
                                                            </span>
                                                            <div class="min-w-0">
                                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-semibold tracking-wider">Phone number</p>
                                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ order.contact_number }}</p>
                                                            </div>
                                                        </div>
 
                                                        <div class="flex items-center gap-3 p-3">
                                                            <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-white dark:bg-slate-800 ring-1 ring-slate-200/80 dark:ring-slate-700 text-slate-500 dark:text-slate-400 shrink-0">
                                                                <i class="fa-regular fa-envelope text-[13px]"></i>
                                                            </span>
                                                            <div class="min-w-0">
                                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 uppercase font-semibold tracking-wider">Email address</p>
                                                                <p class="text-sm font-semibold text-slate-800 dark:text-slate-100 truncate">{{ order.contact_email || '—' }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                                <!-- Shipping Address -->
                                                <div class="p-4 sm:p-5 space-y-3">
                                                    <div class="flex items-center gap-2">
                                                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-slate-50 dark:bg-slate-800 ring-1 ring-slate-200/80 dark:ring-slate-700 text-slate-500 dark:text-slate-400 shrink-0">
                                                            <i class="fa-solid fa-location-dot text-[13px]"></i>
                                                        </span>
                                                        <h3 class="text-[11px] font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">
                                                            Shipping Address
                                                        </h3>
                                                    </div>
 
                                                    <div class="rounded-xl bg-slate-50/80 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 p-3.5 space-y-2.5">
                                                        <p v-if="[order.upazila?.name, order.district?.name, order.division?.name].filter(Boolean).length"
                                                            class="flex flex-wrap items-center gap-x-1.5 gap-y-1 text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                            <template v-for="(part, i) in [order.upazila?.name, order.district?.name, order.division?.name].filter(Boolean)" :key="i">
                                                                <span class="break-words">{{ part }}</span>
                                                                <span v-if="i < [order.upazila?.name, order.district?.name, order.division?.name].filter(Boolean).length - 1" class="text-slate-300 dark:text-slate-600">/</span>
                                                            </template>
                                                        </p>
 
                                                        <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed break-words">
                                                            {{ order.shipping_address }}
                                                        </p>
 
                                                        <div v-if="order.policeStation || order.postal_code" class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-2 border-t border-dashed border-slate-200 dark:border-slate-700">
                                                            <span v-if="order.policeStation" class="inline-flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                                                                <i class="fa-solid fa-building-shield text-[10px]"></i>{{ order.policeStation.name }} PS
                                                            </span>
                                                            <span v-if="order.postal_code" class="inline-flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                                                                <i class="fa-solid fa-mail-bulk text-[10px]"></i>Postal code: {{ order.postal_code }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Remarks -->
                                                <div v-if="order.remarks" class="p-5">
                                                    <div class="w-full flex items-start gap-3.5 pl-4 border-l-2 border-amber-300 dark:border-amber-600 text-left">
                                                        <div class="min-w-0 flex-1 text-left">
                                                            <p class="text-[10.5px] text-amber-600 dark:text-amber-500 uppercase font-bold tracking-wider mb-1 flex items-center gap-1.5 justify-start">
                                                                <i class="fa-regular fa-comment-dots"></i> Note
                                                            </p>
                                                            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed italic text-left">{{ order.remarks }}</p>
                                                        </div>
                                                    </div>
                                                </div>

                                            </div>

                                            <button @click="viewCustomerFullProfile(order)" class="w-full mt-8 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold rounded-xl transition-all border border-transparent hover:border-indigo-200 dark:hover:border-indigo-500/30 text-sm">
                                                View Full Profile
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-5 mt-4 shadow-sm">
                                        <div class="flex items-start gap-3">
                                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center">
                                                <i class="fa-solid fa-circle-info text-blue-600 dark:text-blue-400 text-sm"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-sm text-slate-800 dark:text-slate-100 mb-1">
                                                    Quick Note
                                                </h4>
                                                <p class="text-slate-500 dark:text-slate-400 text-xs leading-relaxed">
                                                    This order was processed through the automated provider. Contact support if transaction ID is missing.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </main>
            </div>
        </div>

    </div>

</template>

<script setup>
import { onMounted, ref, computed, watch } from "vue";
import { useRouter, useRoute } from 'vue-router'
import api, {makeImg} from '../../../../services/api.js'

import Navbar from "../../admin/admin-navbar.vue";
import HeaderSection from "../../admin/admin-header.vue";
import Message from '../../../Message/message.vue'
import FooterSection from "../../../footer.vue";









const mobileMenu = ref(false);

function toggleMenu() {
    mobileMenu.value = !mobileMenu.value;
}









const router = useRouter();
const route = useRoute();
const loading = ref(false);
const successMsg = ref('');
const errorMsg = ref('');












// =============================
// Get orders
// =============================
const order = ref(null);
async function fetchOrderDetails(){
    loading.value = true;
    try{
        const reg = route.params.reg;
        if (!reg) {
            errorMsg.value = "Invalid order reference.";
            return;
        }

        const res = await api.get(`/orders/${route.params.reg}`);
        order.value = res.data.data.order;
        console.log(order.value)
    } catch (err) {
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Something went wrong while fetching order.";
    } finally {
        loading.value = false;
    }
}

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });

const defaultProductImage = "/images/product/default-product.webp";

const getProductImage = (item) => {
    const images = item.product?.images;
    if (images && images.length > 0) {
        return images[0].url;
    }
    return defaultProductImage;
};


















function ProductDetails(item) {
    router.push(`/product-details/${item.product.slug}`)
}

function viewCustomerFullProfile(order){
    router.push(`/admin/customer-details/${order?.user.user_id}`);
}




















// dark and light mode
const isDark = ref(false);
function applyTheme(dark) {
    isDark.value = dark;
    document.documentElement.classList.toggle("dark", dark);
    localStorage.setItem("theme", dark ? "dark" : "light");
}

function toggleTheme() {
    applyTheme(!isDark.value);
}

function toggleDarkMode() {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle("dark",isDark.value);
    localStorage.setItem("theme",isDark.value ? "dark" : "light");
}

/* ESC to close drawer */
onMounted(() => {

    fetchOrderDetails();

    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape") sidebarOpen.value = false;
    });

    const saved = localStorage.getItem("theme");
    if (saved === "dark") applyTheme(true);
    else if (saved === "light") applyTheme(false);
    else applyTheme(window.matchMedia("(prefers-color-scheme: dark)").matches);
});
</script>

<style>

</style>