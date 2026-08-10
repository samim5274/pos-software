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

                    <div v-if="order">

                        <!-- Page header -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-5 border-b border-slate-200 dark:border-slate-800/60">
                            <div class="flex items-center gap-4">
                                <button @click="$router.back()" class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm shrink-0">
                                    <i class="fa-solid fa-arrow-left-long text-slate-600 dark:text-slate-400"></i>
                                </button>

                                <div class="space-y-1.5">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                            Order <span class="text-indigo-600 dark:text-indigo-400">#{{ order.reg }}</span>
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
                                <button class="px-4 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg text-sm font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-50 transition shadow-sm">
                                    Download Invoice
                                </button>
                                <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-semibold shadow-md shadow-indigo-500/20 transition">
                                    Print Details
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
                                            <span class="text-sm text-slate-500 dark:text-slate-400">Transaction ID</span>
                                            <span class="text-sm font-mono font-semibold text-slate-900 dark:text-indigo-300 bg-slate-50 dark:bg-indigo-500/10 px-2.5 py-1 rounded-md">
                                                {{ order.transaction_id || 'Not Available' }}
                                            </span>
                                        </div>
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
                                    </div>
                                </div>

                                <!-- Payment history (separate card, no longer nested inside Transaction Details) -->
                                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm p-6">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-4">Payment History</h3>

                                    <div v-if="payments && payments.length" class="space-y-3">
                                        <div v-for="payment in payments" :key="payment.id"
                                            class="bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800 p-4"
                                            :class="{ 'opacity-70': ['Failed','Cancelled'].includes(payment.status) }">

                                            <div class="flex items-start justify-between">
                                                <div class="flex gap-3">
                                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center shrink-0"
                                                        :class="getPaymentMethod(payment.payment_method).iconBg">
                                                        <i :class="[getPaymentMethod(payment.payment_method).icon, getPaymentMethod(payment.payment_method).iconColor]" class="text-base"></i>
                                                    </div>
                                                    <div>
                                                        <p class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                                                            {{ getPaymentMethod(payment.payment_method).label }}
                                                            <span v-if="payment.gateway" class="font-normal text-slate-400">via {{ payment.gateway }}</span>
                                                        </p>
                                                        <p v-if="payment.transaction_id" class="text-xs font-mono text-slate-400 dark:text-slate-500 mt-0.5">
                                                            {{ payment.transaction_id }}
                                                        </p>
                                                        <p v-else-if="payment.sender_mobile || payment.account_holder_name" class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">
                                                            {{ payment.account_holder_name || 'Sender' }} — {{ payment.sender_mobile }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <span :class="getPaymentStatus(payment.status).badge" class="inline-flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                                    <span class="w-1.5 h-1.5 rounded-full" :class="getPaymentStatus(payment.status).dot"></span>
                                                    {{ payment.status }}
                                                </span>
                                            </div>

                                            <div class="flex justify-between items-center mt-3 pt-3 border-t border-slate-200/70 dark:border-slate-700/60">
                                                <span class="text-xs text-slate-400 dark:text-slate-500">
                                                    {{ payment.paid_at ? formatDate(payment.paid_at) : formatDate(payment.created_at) }}
                                                </span>
                                                <span class="text-sm font-mono font-semibold text-slate-900 dark:text-white">
                                                    {{ payment.currency }} ৳ {{ Number(payment.amount).toLocaleString() }}
                                                </span>
                                            </div>

                                            <div v-if="payment.payment_method === 'bank_transfer' && payment.bank_name"
                                                class="mt-3 pt-3 border-t border-dashed border-slate-200 dark:border-slate-700 text-xs text-slate-500 dark:text-slate-400 grid grid-cols-2 gap-y-1">
                                                <span>Bank</span><span class="text-right font-medium text-slate-700 dark:text-slate-300">{{ payment.bank_name }}</span>
                                                <span>Account</span><span class="text-right font-medium text-slate-700 dark:text-slate-300">{{ payment.account_number }}</span>
                                            </div>

                                            <div v-if="payment.status === 'Pending' && payment.gateway === 'manual'" class="mt-3">
                                                <button @click="verifyPayment(payment)"
                                                        class="w-full h-8 text-xs font-semibold rounded-lg border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition">
                                                    Verify Payment
                                                </button>
                                            </div>
                                            <p v-else-if="payment.verified_at" class="text-[11px] text-slate-400 dark:text-slate-500 mt-2">
                                                Verified {{ formatDate(payment.verified_at) }}
                                            </p>

                                            <p v-if="payment.remarks" class="text-xs text-slate-500 dark:text-slate-400 mt-2 italic">
                                                "{{ payment.remarks }}"
                                            </p>
                                        </div>
                                    </div>

                                    <div v-else class="text-center py-8">
                                        <i class="fa-regular fa-clock text-2xl text-slate-300 dark:text-slate-700"></i>
                                        <p class="text-sm text-slate-400 dark:text-slate-500 mt-2">No payment attempts yet.</p>
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

                                            <div class="text-left space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-user w-4 text-center"></i>
                                                    <span class="font-semibold">{{ order.contact_name }}</span>
                                                </div>

                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-phone w-4 text-center"></i>
                                                    <span>{{ order.contact_number }}</span>
                                                </div>

                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-envelope w-4 text-center"></i>
                                                    <span>{{ order.contact_email }}</span>
                                                </div>

                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-location-dot w-4 text-center mt-0.5"></i>
                                                    <span class="leading-relaxed">{{ order.shipping_address }}</span>
                                                </div>

                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-money-bill-transfer w-4 text-center mt-0.5"></i>
                                                    <span class="leading-relaxed">{{ order.transaction_id }}</span>
                                                </div>

                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-phone w-4 text-center mt-0.5 text-slate-400 dark:text-slate-500"></i>
                                                    <span class="leading-relaxed">
                                                        <span class="font-medium text-slate-500 dark:text-slate-500 mr-1">Acc. No:</span>
                                                        {{ order.payment_number }}
                                                    </span>
                                                </div>

                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400 mt-1.5">
                                                    <i class="fa-solid fa-money-bill-transfer w-4 text-center mt-0.5 text-slate-400 dark:text-slate-500"></i>
                                                    <span class="leading-relaxed">
                                                        <span class="font-medium text-slate-500 dark:text-slate-500 mr-1">TrxID:</span>
                                                        {{ order.payment_transaction_code }}
                                                    </span>
                                                </div>

                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-comment-dots w-4 text-center mt-0.5"></i>
                                                    <span class="leading-relaxed">
                                                        <span class="text-xs text-slate-400 uppercase font-bold mr-1">Note:</span>
                                                        {{ order.remarks || 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <button @click="viewCustomerFullProfile(order)" class="w-full mt-8 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold rounded-xl transition-all border border-transparent hover:border-indigo-200 dark:hover:border-indigo-500/30 text-sm">
                                                View Full Profile
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bg-indigo-600 rounded-2xl p-6 mt-4 text-white shadow-lg shadow-indigo-500/30">
                                        <h4 class="font-bold mb-2 flex items-center gap-2">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Quick Note
                                        </h4>
                                        <p class="text-indigo-100 text-xs leading-relaxed">
                                            This order was processed through the automated gateway. Contact support if transaction ID is missing.
                                        </p>
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

</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { useRouter, useRoute } from "vue-router";
import api, {makeImg} from '../../../services/api';

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
    };
    return map[method] || map.cod;
}

function getPaymentStatus(status) {
    const map = {
        Pending: {
            badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400',
            dot: 'bg-slate-400',
        },
        Processing: {
            badge: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400',
            dot: 'bg-blue-500',
        },
        Success: {
            badge: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
            dot: 'bg-emerald-500',
        },
        Failed: {
            badge: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400',
            dot: 'bg-red-500',
        },
        Cancelled: {
            badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-500 dark:text-slate-400',
            dot: 'bg-slate-400',
        },
        Refunded: {
            badge: 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400',
            dot: 'bg-purple-500',
        },
    };
    return map[status] || map.Pending;
}




















// FIX: this function was called from the template ("Verify Payment" button)
async function verifyPayment(paymentRecord) {
    try {
        loading.value = true;
        const res = await api.post(`/orders/payments/${paymentRecord.id}/verify`);
        if (res.data.success) {
            paymentRecord.status = 'Success';
            paymentRecord.verified_at = new Date().toISOString();
            successMsg.value = res.data.message || 'Payment verified.';
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




















function ProductDetails(item) {
    router.push(`/product-details/${item.product.slug}`)
}






















function viewCustomerFullProfile(order){
    router.push(`/admin/customer-details/${order?.user.user_id}`);
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