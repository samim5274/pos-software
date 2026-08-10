<template>
    <div class="min-h-screen bg-white dark:bg-slate-950 transition-colors duration-200">
        <Header
        @open-sidebar="sidebarOpen = true"
        @search="onSearch"
        :isDark="isDark" @toggle-theme="toggleTheme"
        />

        <div class="flex  min-h-[calc(100vh-56px)]">
            <Navbar
                v-model="active"
                :open="sidebarOpen"
                @close="sidebarOpen = false"
            />

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
                                            Order <span class="text-green-600 dark:text-orange-400">#{{ order.reg }}</span>
                                        </h1>

                                        <span
                                            v-if="order.coupon_id"
                                            class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-mono text-xs font-semibold px-2 py-0.5 rounded-full border border-amber-200/60 dark:border-amber-900/50 uppercase tracking-wider"
                                            title="Coupon Applied" >
                                            <i class="fa-solid fa-tags text-amber-500"></i>
                                            {{ order.coupon_code }}
                                        </span>
                                    </div>

                                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-regular fa-calendar text-slate-400 dark:text-slate-500"></i>
                                        Placed on {{ formatDate(order.date) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <button @click="downloadInvoice" :disabled="invoiceDownloading"
                                    class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-sm">
                                    <i class="fa-solid fa-download mr-1.5"></i>
                                    {{ invoiceDownloading ? 'Preparing...' : 'Download Invoice' }}
                                </button>
                                <button @click="printOrder(order)" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-md shadow-indigo-500/20 transition">
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

                                    <div
                                        @click="!(order.status === 'cancelled' || order.status === 'delivered') && openStatusModal()"
                                        :class="(order.status === 'cancelled' || order.status === 'delivered')
                                            ? 'opacity-50 cursor-not-allowed'
                                            : 'cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500'"
                                        class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm transition-all group flex flex-col justify-between">
                                        <div class="flex justify-between items-center mb-3">
                                            <div class="flex items-center gap-2">
                                                <i class="fa-solid fa-truck-fast text-slate-400 text-sm"></i>
                                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Order Status</p>
                                            </div>
                                            <i class="fa-solid fa-pencil h-4 w-4 text-slate-400 opacity-0 group-hover:opacity-100 transition"></i>
                                        </div>
                                        <span :class="getStatus(order.status).container" class="px-3 py-1 rounded-lg text-[11px] font-bold uppercase inline-flex items-center gap-2 border border-transparent dark:border-current/10 w-fit">
                                            <span class="h-2 w-2 rounded-full" :class="getStatus(order.status).dot"></span>
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

                                <!-- Order timeline -->
                                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-6">Order Timeline</h3>
                                    <div class="flex items-start overflow-x-auto pb-1 -mx-1 px-1">
                                        <template v-for="(step, idx) in timelineSteps" :key="step.key">
                                            <div class="flex flex-col items-center text-center min-w-[86px] shrink-0">
                                                <div class="w-9 h-9 rounded-full flex items-center justify-center border-2" :class="stepClass(step)">
                                                    <i :class="step.icon" class="text-xs"></i>
                                                </div>
                                                <p class="text-[11px] font-semibold mt-2 leading-tight"
                                                    :class="step.reached ? 'text-slate-700 dark:text-slate-300' : 'text-slate-400 dark:text-slate-600'">
                                                    {{ step.label }}
                                                </p>
                                                <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                    {{ step.at ? formatDate(step.at) : (step.reached ? 'Reached' : 'Pending') }}
                                                </p>
                                            </div>
                                            <div v-if="idx < timelineSteps.length - 1"
                                                class="flex-1 h-0.5 mt-[18px] mx-1 min-w-[20px]"
                                                :class="step.reached ? 'bg-indigo-400 dark:bg-indigo-500' : 'bg-slate-200 dark:bg-slate-700'"></div>
                                        </template>
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


    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="isStatusModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">

                <div
                    @click.stop
                    class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white">Update Order Status</h3>
                        <button @click="isStatusModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                            <i class="fa-solid fa-x h-6 w-6"></i>
                        </button>
                    </div>

                    <div class="p-6 space-y-3">
                        <p class="text-sm text-slate-500 mb-4">Select the new status for order #{{ order.reg }}</p>

                        <button
                            v-for="(config, statusName) in statusConfig"
                            :key="statusName"
                            v-show="statusName !== 'default'"
                            @click="updateStatus(statusName)"
                            class="w-full flex items-center justify-between p-3 rounded-xl border transition-all"
                            :class="[
                                order.status.toLowerCase() === statusName.toLowerCase()
                                    ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10'
                                    : 'border-slate-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800'
                            ]">

                            <span :class="config.container" class="px-3 py-1 rounded-lg text-[10px] font-bold uppercase inline-flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full" :class="config.dot"></span>
                                {{ statusName }}
                            </span>

                            <div v-if="order.status.toLowerCase() === statusName.toLowerCase()" class="text-indigo-600">
                                <i class="fa-solid fa-check h-5 w-5"></i>
                            </div>
                        </button>
                    </div>

                    <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/50 flex justify-end">
                        <button @click="isStatusModalOpen = false" class="text-sm font-semibold text-slate-600 dark:text-slate-400 hover:underline">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>

    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div
                @click.stop
                class="bg-white dark:bg-slate-900 w-full max-w-md rounded-2xl shadow-2xl border border-slate-200 dark:border-slate-800 overflow-hidden">

                <!-- Header -->
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Make payment</h3>
                    <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Order #{{ order?.reg }}</p>
                </div>
                <button @click="isPaymentModalOpen = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                    <i class="fa-solid fa-x h-5 w-5"></i>
                </button>
                </div>

                <form @submit.prevent="submitPayment" class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">

                <!-- Amount -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                    Amount <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-slate-400">৳</span>
                    <input
                        v-model="paymentForm.amount"
                        type="number"
                        step="0.01"
                        min="1"
                        max="99999999.99"
                        required
                        class="w-full h-11 pl-7 pr-3 text-sm font-mono rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                </div>

                <!-- Payment method -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                    Payment method <span class="text-red-500">*</span>
                    </label>
                    <select
                    v-model="paymentForm.payment_method"
                    required
                    class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500">
                    <option value="cod">Cash on delivery</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank transfer</option>
                    <option value="mobile_banking">Mobile banking</option>
                    <option value="card">Card</option>
                    <option value="paypal">PayPal</option>
                    <option value="wallet">Wallet</option>
                    </select>
                </div>

                <!-- Bank transfer -->
                <div v-if="paymentForm.payment_method === 'bank_transfer'" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Bank name <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.bank_name" required maxlength="150" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Account number <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.account_number" required maxlength="100" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                    <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Account holder name <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.account_holder_name" required maxlength="150" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                    <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.transaction_id" required maxlength="100" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                </div>

                <!-- Mobile banking -->
                <div v-if="paymentForm.payment_method === 'mobile_banking'" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Provider <span class="text-red-500">*</span>
                    </label>
                    <select v-model="paymentForm.provider" required class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500">
                        <option value="" selected disabled>-- Select provider --</option>
                        <option value="bkash">bKash</option>
                        <option value="nagad">Nagad</option>
                        <option value="rocket">Rocket</option>
                    </select>
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Sender mobile <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.sender_mobile" required maxlength="20" placeholder="01xxxxxxxxx" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Sender name <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.sender_name" required maxlength="150" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.transaction_id" required maxlength="100" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                </div>

                <!-- Card -->
                <div v-if="paymentForm.payment_method === 'card'" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Gateway <span class="text-red-500">*</span>
                    </label>
                    <select v-model="paymentForm.provider" required class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500">
                        <option value="">Select gateway</option>
                        <option value="sslcommerz">SSLCommerz</option>
                        <option value="stripe">Stripe</option>
                    </select>
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Gateway transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.transaction_id" required maxlength="100" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                </div>

                <!-- PayPal -->
                <div v-if="paymentForm.payment_method === 'paypal'" class="grid grid-cols-1 md:grid-cols-2 gap-3 p-3 rounded-xl bg-slate-50/70 dark:bg-slate-800/30 border border-slate-100 dark:border-slate-800">
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        Provider <span class="text-red-500">*</span>
                    </label>
                    <!-- provider is force-set to 'paypal' by a watcher on payment_method, not mutated inline here -->
                    <input :value="paymentForm.provider" disabled
                        class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400" />
                    </div>
                    <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                        PayPal transaction ID <span class="text-red-500">*</span>
                    </label>
                    <input v-model="paymentForm.transaction_id" required maxlength="100" class="w-full h-11 px-3 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500" />
                    </div>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1.5">
                    Remarks <span class="font-normal text-slate-400">(optional)</span>
                    </label>
                    <textarea
                    v-model="paymentForm.remarks"
                    rows="2"
                    maxlength="1000"
                    placeholder="e.g. Advance payment"
                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 resize-none"></textarea>
                </div>

                <p v-if="paymentFormError" class="text-xs text-red-500 bg-red-50 dark:bg-red-950/30 rounded-lg px-3 py-2">{{ paymentFormError }}</p>

                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" @click="isPaymentModalOpen = false" class="px-4 py-2.5 text-sm font-semibold text-slate-600 dark:text-slate-400 hover:underline">
                    Cancel
                    </button>
                    <button type="submit" :disabled="paymentSubmitting"
                    class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-xl shadow-md shadow-indigo-500/20 transition">
                    {{ paymentSubmitting ? 'Processing...' : 'Confirm payment' }}
                    </button>
                </div>
                </form>
            </div>
            </div>
        </Transition>
    </Teleport>

</template>

<script setup>
import { onMounted, ref, computed, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
import api, {makeImg} from '../../../services/api';
import { UAParser } from 'ua-parser-js'

import Navbar from '../admin/admin-navbar.vue';
import Header from '../admin/admin-header.vue';
import Message from '../../Message/message.vue';

const sidebarOpen = ref(false);
const active = ref("dashboard");

const router = useRouter();
const route = useRoute();

const loading = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

// =============================
// Get orders
// =============================
const order = ref(null);
const payments = ref(null);
const deliveryCharge = ref(null);
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
        const paymentData = res.data.data.payment;
        payments.value = Array.isArray(paymentData) ? paymentData : (paymentData ? [paymentData] : []);
        deliveryCharge.value = res.data.data.deliveryCharge;
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

const statusConfig = {
    'Pending': {
        container: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
        dot: 'bg-amber-500'
    },
    'Confirmed': {
        container: 'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400',
        dot: 'bg-sky-500'
    },
    'Processing': {
        container: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400',
        dot: 'bg-indigo-500'
    },
    'Picked': {
        container: 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400',
        dot: 'bg-violet-500'
    },
    'Shipped': {
        container: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
        dot: 'bg-blue-500'
    },
    'Out for Delivery': {
        container: 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
        dot: 'bg-orange-500'
    },
    'Delivered': {
        container: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
        dot: 'bg-emerald-500'
    },
    'Cancelled': {
        container: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
        dot: 'bg-rose-500'
    },
    'Failed': {
        container: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
        dot: 'bg-red-600'
    },
    'Returned': {
        container: 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-400',
        dot: 'bg-slate-500'
    },
    default: {
        container: 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
        dot: 'bg-gray-500'
    }
};

const getStatus = (status) => {
    if (!status) return statusConfig.default;
    const matchedKey = Object.keys(statusConfig).find(
        key => key.toLowerCase() === status.toLowerCase()
    );
    return statusConfig[matchedKey] || statusConfig.default;
};


const photoUrl = computed(() => {
    const p = order.value?.user?.photo;
    if (!p) return "/images/avatar.png";
    return makeImg(p);
});

function statusStyle(status) {
    const map = {
        Pending: {
            badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400',
            icon: 'fa-regular fa-clock',
        },
        Partial: {
            badge: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400',
            icon: 'fa-solid fa-circle-half-stroke',
        },
        Paid: {
            badge: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
            icon: 'fa-regular fa-circle-check',
        },
        Failed: {
            badge: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
            icon: 'fa-regular fa-circle-xmark',
        },
        Refunded: {
            badge: 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400',
            icon: 'fa-solid fa-rotate-left',
        },
    };
    return map[status] || map.Pending;
}

// Payment method display config
function getPaymentMethod(method) {
    const map = {
        cod: {
            label: 'Cash on delivery',
            icon: 'fa-solid fa-money-bill-wave',
            iconBg: 'bg-slate-100 dark:bg-slate-500/10',
            iconColor: 'text-slate-500 dark:text-slate-400',
        },
        cash: {
            label: 'Cash',
            icon: 'fa-solid fa-money-bill-1-wave',
            iconBg: 'bg-slate-100 dark:bg-slate-500/10',
            iconColor: 'text-slate-500 dark:text-slate-400',
        },
        bank_transfer: {
            label: 'Bank transfer',
            icon: 'fa-solid fa-building-columns',
            iconBg: 'bg-amber-50 dark:bg-amber-500/10',
            iconColor: 'text-amber-600 dark:text-amber-400',
        },
        mobile_banking: {
            label: 'Mobile banking',
            icon: 'fa-solid fa-mobile-screen-button',
            iconBg: 'bg-emerald-50 dark:bg-emerald-500/10',
            iconColor: 'text-emerald-600 dark:text-emerald-400',
        },
        card: {
            label: 'Card',
            icon: 'fa-regular fa-credit-card',
            iconBg: 'bg-indigo-50 dark:bg-indigo-500/10',
            iconColor: 'text-indigo-600 dark:text-indigo-400',
        },
        paypal: {
            label: 'PayPal',
            icon: 'fa-brands fa-paypal',
            iconBg: 'bg-blue-50 dark:bg-blue-500/10',
            iconColor: 'text-blue-600 dark:text-blue-400',
        },
        wallet: {
            label: 'Wallet',
            icon: 'fa-solid fa-wallet',
            iconBg: 'bg-purple-50 dark:bg-purple-500/10',
            iconColor: 'text-purple-600 dark:text-purple-400',
        },
    };
    return map[method] || map.cod;
}

function getPaymentStatus(status) {
    const map = {
        Pending: {
            badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400',
            dot: 'bg-slate-400',
            accentBar: 'bg-slate-300 dark:bg-slate-600',
        },
        Processing: {
            badge: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
            dot: 'bg-blue-500',
            accentBar: 'bg-blue-400',
        },
        Success: {
            badge: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
            dot: 'bg-emerald-500',
            accentBar: 'bg-emerald-500',
        },
        Failed: {
            badge: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
            dot: 'bg-red-500',
            accentBar: 'bg-red-400',
        },
        Cancelled: {
            badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-500 dark:text-slate-400',
            dot: 'bg-slate-400',
            accentBar: 'bg-slate-300',
        },
        Refunded: {
            badge: 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400',
            dot: 'bg-purple-500',
            accentBar: 'bg-purple-400',
        },
    };
    return map[status] || map.Pending;
}

const parseUserAgent = (ua) => {
    const parser = new UAParser(ua)
    const result = parser.getResult()

    return {
        browser: `${result.browser.name || 'Unknown'} ${result.browser.version || ''}`,
        os: `${result.os.name || 'Unknown'} ${result.os.version || ''}`,
        device: result.device.type || 'Desktop',
    }
}

// =============================
// Paid / Due summary — computed from the payments relation
// (Order.paid_amount / due_amount are not relied on; the payments
// array is the source of truth for what has actually settled.)
// =============================
const totalPaid = computed(() => {
    if (!payments.value || !payments.value.length) return 0;
    return payments.value.reduce((sum, p) => {
        if (p.status !== 'Success') return sum;
        const amt = Number(p.amount) || 0;
        return p.payment_type === 'Refund' ? sum - amt : sum + amt;
    }, 0);
});

const dueAmount = computed(() => {
    const payable = Number(order.value?.payable_amount) || 0;
    return Math.max(payable - totalPaid.value, 0);
});

// =============================
// Order timeline
// =============================
// Mirrors the full `status` enum on the orders table. The happy path
// (Pending → ... → Delivered) has no dedicated `out_for_delivery_at`
// column, so that step is inferred from status position instead of a
// timestamp. Cancelled / Failed / Returned are terminal deviations that
// can happen from any point in the happy path, so they get appended
// on top of whichever steps were actually reached.
const STATUS_SEQUENCE = ['Pending', 'Confirmed', 'Processing', 'Picked', 'Shipped', 'Out for Delivery', 'Delivered'];

const TERMINAL_META = {
    Cancelled: { icon: 'fa-solid fa-circle-xmark', style: 'border-red-500 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400', atField: 'cancelled_at' },
    Failed: { icon: 'fa-solid fa-triangle-exclamation', style: 'border-red-500 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400', atField: null },
    Returned: { icon: 'fa-solid fa-rotate-left', style: 'border-amber-500 bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400', atField: null },
};

const timelineSteps = computed(() => {
    if (!order.value) return [];
    const o = order.value;
    const currentIndex = STATUS_SEQUENCE.indexOf(o.status);
    const terminal = TERMINAL_META[o.status] || null;

    const steps = [
        { key: 'placed', label: 'Placed', at: o.date, icon: 'fa-solid fa-cart-shopping' },
        { key: 'confirmed', label: 'Confirmed', at: o.confirmed_at, icon: 'fa-solid fa-circle-check' },
        { key: 'processing', label: 'Processing', at: o.processing_at, icon: 'fa-solid fa-gears' },
        { key: 'picked', label: 'Picked', at: o.picked_at, icon: 'fa-solid fa-box' },
        { key: 'shipped', label: 'Shipped', at: o.shipped_at, icon: 'fa-solid fa-truck' },
        { key: 'out_for_delivery', label: 'Out for delivery', at: o.out_for_delivery_at ?? null, icon: 'fa-solid fa-truck-fast' },
        { key: 'delivered', label: 'Delivered', at: o.delivered_at, icon: 'fa-solid fa-house-circle-check' },
    ].map((s, idx) => ({
        ...s,
        // reached if it has its own timestamp, or the order's current
        // status has already passed this step in the sequence
        reached: idx === 0 || !!s.at || (currentIndex >= 0 && idx <= currentIndex),
    }));

    if (terminal) {
        const reached = steps.filter(s => s.reached);
        reached.push({
            key: 'terminal',
            label: o.status,
            at: terminal.atField ? o[terminal.atField] : null,
            reached: true,
            icon: terminal.icon,
            style: terminal.style,
        });
        return reached;
    }

    return steps;
});

function stepClass(step) {
    if (step.style) return step.style;
    if (step.reached) return 'border-indigo-500 bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400';
    return 'border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-300 dark:text-slate-600';
}

// FIX: this function was called from the template ("Verify Payment" button)
async function verifyPayment(paymentRecord) {
    const confirmed = confirm(
        "Are you sure you want to verify this payment?"
    );

    if (!confirmed) {
        return;
    }

    try {
        loading.value = true;
        const res = await api.post(`/orders/payments/${paymentRecord.id}/verify`);
        if (res.data.success) {
            paymentRecord.status = 'Success';
            paymentRecord.verified_at = new Date().toISOString();
            successMsg.value = res.data.message || 'Payment verified.';
            fetchOrderDetails();
        } else {
            errorMsg.value = res.data.message;
        }
    } catch (err) {
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Something went wrong while verifying payment.";
    } finally {
        loading.value = false;
    }
}

// open pop-up
const isStatusModalOpen = ref(false);

function openStatusModal() {
    const status = order.value?.status?.toLowerCase();

    if (status === "cancelled" || status === "delivered") {
        errorMsg.value = "This order status cannot be modified.";
        return;
    }

    isStatusModalOpen.value = true;
}

async function updateStatus(newStatus){
    try{
        loading.value = true;
        const res = await api.post(`/orders/update-status/${route.params.reg}`, {
            status: newStatus.toLowerCase()
        });
        if (res.data.success) {
            order.value.status = newStatus;
            successMsg.value = res.data.message;
        } else {
            errorMsg.value = res.data.message;
        }
    } catch (err) {
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Something went wrong while updating status.";
    } finally {
        loading.value = false;

        setTimeout(() => {
            isStatusModalOpen.value = false;
        }, 200);
    }
}

// Get order cart items
const cartItems = ref([]);
async function getCartItems() {
    loading.value = true;
    try {
        const res = await api.get(`/cart/${route.params.reg}`);
        cartItems.value = res.data.data;
    } catch (err) {
        console.error(err);
        errorMsg.value = err || "Something is wrong";
    } finally {
        loading.value = false;
    }
}

const defaultProductImage = "/images/product/default-product.webp";

const getProductImage = (item) => {
    const images = item.product?.images;
    if (images && images.length > 0) {
        return images[0].url;
    }
    return defaultProductImage;
};

const isPaymentModalOpen = ref(false);
const paymentSubmitting = ref(false);
const paymentFormError = ref('');

const paymentForm = ref({
    amount: '',
    payment_method: 'cash',
    provider: '',
    transaction_id: '',
    sender_name: '',
    sender_mobile: '',
    bank_name: '',
    account_number: '',
    account_holder_name: '',
    remarks: '',
});

// FIX: the previous version mutated paymentForm.provider inside a template
// expression ( :value="paymentForm.provider = 'paypal'" ), which is an
// anti-pattern in Vue. A watcher now owns that side effect.
watch(() => paymentForm.value.payment_method, (method) => {
    if (method === 'paypal') {
        paymentForm.value.provider = 'paypal';
    } else if (paymentForm.value.provider === 'paypal') {
        paymentForm.value.provider = '';
    }
});

function resetPaymentForm() {
    paymentForm.value = {
        amount: order.value?.payable_amount ?? '',
        payment_method: 'cash',
        provider: '',
        transaction_id: '',
        sender_name: '',
        bank_name: '',
        account_number: '',
        account_holder_name: '',
        sender_mobile: '',
        remarks: '',
    };
    paymentFormError.value = '';
}

function openPaymentModal() {
    resetPaymentForm();
    isPaymentModalOpen.value = true;
}

async function submitPayment() {
    paymentFormError.value = '';

    if (!paymentForm.value.amount || Number(paymentForm.value.amount) <= 0) {
        paymentFormError.value = 'Please enter a valid amount.';
        return;
    }

    paymentSubmitting.value = true;
    try {
        const res = await api.post(`/orders/${route.params.reg}/payments`, {
            ...paymentForm.value
        });

        if (res.data.success) {
            const newPayment = res.data.data.payment ?? res.data.data;
            payments.value = payments.value && payments.value.length
                ? [newPayment, ...payments.value]
                : [newPayment];

            successMsg.value = res.data.message || 'Payment recorded successfully.';
            isPaymentModalOpen.value = false;
            fetchOrderDetails();
        } else {
            paymentFormError.value = res.data.message || 'Something went wrong.';
        }
    } catch (err) {
        paymentFormError.value =
            err.response?.data?.message ||
            err.message ||
            'Something went wrong while recording the payment.';
    } finally {
        paymentSubmitting.value = false;
    }
}

function ProductDetails(item) {
    router.push(`/product-details/${item.product.slug}`)
}

function viewCustomerFullProfile(order){
    router.push(`/admin/customer-details/${order?.user.user_id}`);
}

















const statusDropdownOpen = ref(false);
const statusUpdating = ref(false);

const statusOptions = [
    { value: 'pending' },
    { value: 'success' },
    { value: 'return' },
    { value: 'failed' },
];

async function updateDeliveryStatus(newStatus) {
    if (newStatus === deliveryCharge.value.payment_status) {
        statusDropdownOpen.value = false;
        return;
    }

    const previousStatus = deliveryCharge.value.payment_status;
    deliveryCharge.value.payment_status = newStatus;
    statusDropdownOpen.value = false;
    statusUpdating.value = true;

    try {
        await api.patch(`/orders/delivery-charge-payments/${deliveryCharge.value.id}/status`, {
            payment_status: newStatus,
        });
    } catch (err) {
        deliveryCharge.value.payment_status = previousStatus;
        errorMsg.value = err.response?.data?.message || err.message || 'Failed to update status.';
    } finally {
        statusUpdating.value = false;
    }
}

function handleClickOutside(event) {
    if (!event.target.closest('.status-dropdown')) {
        statusDropdownOpen.value = false;
    }
}

















// =============================
// Print / Download invoice
// =============================
function printOrder(order) {
    const win = window.open("about:blank", "_blank");
    if(!win){
        alert("Popup blocked! Allow popups.");
        return;
    }
    console.log(order)
    win.location.href = `/admin/order/invoice-print/${order.reg}`;
}

const invoiceDownloading = ref(false);
async function downloadInvoice() {
    // NOTE: adjust this endpoint to whatever your backend actually exposes
    // for generating the invoice PDF.
    invoiceDownloading.value = true;
    try {
        const res = await api.get(`/orders/${route.params.reg}/invoice`, { responseType: 'blob' });
        const url = window.URL.createObjectURL(new Blob([res.data]));
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('download', `invoice-${order.value.reg}.pdf`);
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.URL.revokeObjectURL(url);
    } catch (err) {
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Could not download the invoice.";
    } finally {
        invoiceDownloading.value = false;
    }
}























const isDark = ref(false);

function applyTheme(dark) {
    isDark.value = dark;
    document.documentElement.classList.toggle("dark", dark);
    localStorage.setItem("theme", dark ? "dark" : "light");
}

function toggleTheme() {
    applyTheme(!isDark.value);
}

function onSearch(q) {
    console.log("search:", q);
}

/* ESC to close drawer */
onMounted(() => {

    fetchOrderDetails();
    getCartItems();

    window.addEventListener("keydown", (e) => {
        if (e.key === "Escape") sidebarOpen.value = false;
    });

    const saved = localStorage.getItem("theme");

    if (saved === "dark") applyTheme(true);
    else if (saved === "light") applyTheme(false);
    else {
        const systemDark = window.matchMedia("(prefers-color-scheme: dark)").matches;
        applyTheme(systemDark);
    }
});
</script>

<style>

</style>