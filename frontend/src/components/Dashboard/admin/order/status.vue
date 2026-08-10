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
                <main class="flex-1 min-h-screen min-w-0 bg-gray-50 dark:bg-[#0C1326] px-4 sm:px-6 lg:px-8 py-6">

                    <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h1 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Status Order Filter</h1>
                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-500/20">
                                    {{ orders.length }} Orders
                                </span>
                            </div>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Monitor and manage your customer transactions and shipping status.</p>
                        </div>
                    </div>

                    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                            
                            <div class="relative flex-1">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    <i class="fa-solid fa-magnifying-glass h-4 w-4"></i>
                                </div>
                                <input 
                                    type="text" 
                                    v-model="searchQuery" 
                                    placeholder="Search by ID, Customer name or Transaction..." 
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 py-2.5 pl-11 pr-4 text-sm text-slate-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:focus:border-indigo-500"
                                />
                            </div>

                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex items-center gap-2">
                                    <i class="fa-solid fa-filter h-4 w-4 text-slate-400"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Status:</span>
                                </div>
                                <select 
                                    v-model="statusFilter" 
                                    class="min-w-[160px] rounded-xl border border-slate-200 bg-slate-50 py-2.5 px-4 text-sm font-semibold text-slate-700 outline-none transition-all focus:border-indigo-500 focus:bg-white dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:focus:border-indigo-500"
                                >
                                    <option value="">All Statuses</option>
                                    <option value="pending">Pending</option>
                                    <option value="confirmed">Confirmed</option>
                                    <option value="processing">Processing</option>
                                    <option value="picked">Picked</option>
                                    <option value="shipped">Shipped</option>
                                    <option value="out for delivery">Out for Delivery</option>
                                    <option value="delivered">Delivered</option>
                                    <option value="cancelled">Cancelled</option>
                                    <option value="failed">Failed</option>
                                    <option value="returned">Returned</option>
                                </select>

                                <button 
                                    @click="resetFilters" 
                                    class="p-2.5 text-slate-400 hover:text-rose-500 transition-colors"
                                    title="Reset Filters"
                                >
                                    <i class="fa-solid fa-rotate h-5 w-5"></i>
                                </button>
                            </div>
                        </div>
                    </div>


                    
                    <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto max-h-[850px] scrollbar-thin">
                            <table class="w-full text-left border-collapse whitespace-nowrap">
                                <!-- Table Header -->
                                <thead class="bg-slate-50/80 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 sticky top-0 backdrop-blur-md z-10">
                                    <tr>
                                        <th class="pl-5 pr-4 py-3 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-[14%]">Order / Reg</th>
                                        <th class="px-4 py-3 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-[26%]">Customer & Contact</th>
                                        <th class="px-4 py-3 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-[16%]">Timeline</th>
                                        <th class="px-4 py-3 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-[16%]">Payment & Gateway</th>
                                        <th class="px-4 py-3 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-[14%] text-right">Financials (BDT)</th>
                                        <th class="pl-4 pr-5 py-3 text-[11px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest w-[14%] text-center">Order Status</th>
                                    </tr>
                                </thead>

                                <!-- Table Body -->
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/50">
                                    
                                    <!-- Loading State -->
                                    <tr v-if="loading">
                                        <td colspan="6" class="px-6 py-20 text-center">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <div class="h-6 w-6 rounded-full border-2 border-indigo-600/20 dark:border-indigo-400/20 border-t-indigo-600 dark:border-t-indigo-400 animate-spin"></div>
                                                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 tracking-wide">Syncing daily orders...</p>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    <!-- Data Rows -->
                                    <template v-else-if="orders.length > 0"">
                                        <tr v-for="order in orders"
                                            :key="order.id" 
                                            @click="viewOrderDetails(order)" 
                                            :class="[
                                                'hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-all duration-150 cursor-pointer group border-l-4 border-l-transparent',
                                                order.status.toLowerCase() === 'pending' ? 'hover:border-l-amber-500' :
                                                order.status.toLowerCase() === 'delivered' ? 'hover:border-l-emerald-500' :
                                                order.status.toLowerCase() === 'cancelled' ? 'hover:border-l-rose-500' : 'hover:border-l-indigo-500'
                                            ]"
                                        >
                                            <!-- Order Identification & Coupon -->
                                            <td class="pl-5 pr-4 py-2.5">
                                                <div class="font-mono text-xs font-bold text-slate-900 dark:text-slate-100 tracking-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                    {{ order.reg }}
                                                </div>
                                                <div v-if="order.coupon_code" class="text-[10px] text-indigo-600 dark:text-indigo-400 font-mono mt-0.5 flex items-center gap-1">
                                                    <i class="fa-solid fa-tag text-[9px]"></i>
                                                    {{ order.coupon_code }}
                                                </div>
                                                <div v-else class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                    No Coupon
                                                </div>
                                            </td>

                                            <!-- Customer & Contact Details -->
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center gap-2.5">
                                                    <!-- Initials Avatar -->
                                                    <div class="h-7 w-7 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 flex items-center justify-center font-bold text-[10px] uppercase border border-slate-200/50 dark:border-slate-700/40">
                                                        {{ order.user?.name ? order.user.name.substring(0, 2) : (order.contact_name ? order.contact_name.substring(0, 2) : 'CU') }}
                                                    </div>
                                                    <div class="truncate max-w-[190px]">
                                                        <div class="text-xs font-semibold text-slate-800 dark:text-slate-200 truncate">
                                                            {{ order.user?.name || order.contact_name || 'Guest Customer' }}
                                                        </div>
                                                        <div class="text-[10px] text-slate-400 dark:text-slate-500 font-mono mt-0.5 flex items-center gap-1">
                                                            <span>{{ order.contact_number || 'No Phone' }}</span>
                                                            <span v-if="order.user?.user_id" class="text-slate-300 dark:text-slate-700">|</span>
                                                            <span v-if="order.user?.user_id">ID: {{ order.user.user_id }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Date & Timeline Progress -->
                                            <td class="px-4 py-2.5">
                                                <div class="text-xs text-slate-600 dark:text-slate-400 font-medium">
                                                    {{ formatDate(order.date) }}
                                                </div>
                                                <div v-if="order.delivered_at" class="text-[10px] text-emerald-600 dark:text-emerald-500 font-medium mt-0.5 flex items-center gap-1">
                                                    <i class="fa-solid fa-circle-check text-[9px]"></i> Del: {{ formatDate(order.delivered_at) }}
                                                </div>
                                                <div v-else-if="order.confirmed_at" class="text-[10px] text-sky-600 dark:text-sky-500 font-medium mt-0.5">
                                                    Confirmed
                                                </div>
                                                <div v-else class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                    Placed
                                                </div>
                                            </td>

                                            <!-- Payment Method & Payment Status -->
                                            <td class="px-4 py-2.5">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-xs font-bold uppercase tracking-wide text-slate-700 dark:text-slate-300">
                                                        {{ order.payment_method }}
                                                    </span>
                                                </div>
                                                <!-- Payment Status Badges based on model constants -->
                                                <div class="text-[10px] font-semibold mt-0.5 flex items-center gap-1">
                                                    <span class="h-1 w-1 rounded-full" 
                                                        :class="{
                                                            'bg-emerald-500': order.payment_status === 'Paid',
                                                            'bg-amber-500': order.payment_status === 'Pending',
                                                            'bg-blue-500': order.payment_status === 'Partial',
                                                            'bg-rose-500': order.payment_status === 'Failed' || order.payment_status === 'Refunded',
                                                        }"
                                                    ></span>
                                                    <span :class="{
                                                        'text-emerald-600 dark:text-emerald-400': order.payment_status === 'Paid',
                                                        'text-amber-600 dark:text-amber-400': order.payment_status === 'Pending',
                                                        'text-blue-600 dark:text-blue-400': order.payment_status === 'Partial',
                                                        'text-rose-600 dark:text-rose-400': order.payment_status === 'Failed' || order.payment_status === 'Refunded',
                                                    }">
                                                        {{ order.payment_status }}
                                                    </span>
                                                </div>
                                            </td>

                                            <!-- Financials (Payable Amount, Base Amount & Discounts) -->
                                            <td class="px-4 py-2.5 text-right">
                                                <div class="text-xs font-bold text-slate-900 dark:text-slate-50 tabular-nums">
                                                    ৳{{ parseFloat(order.payable_amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                                                </div>
                                                <!-- Show discount summary if exists -->
                                                <div v-if="parseFloat(order.coupon_discount) > 0 || parseFloat(order.discount) > 0" class="text-[9px] text-rose-500 font-medium mt-0.5">
                                                    Saved ৳{{ (parseFloat(order.coupon_discount) + parseFloat(order.discount)).toFixed(0) }}
                                                </div>
                                                <div v-else class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                    Base: ৳{{ parseFloat(order.amount).toFixed(0) }}
                                                </div>
                                            </td>

                                            <!-- Order Status Badge -->
                                            <td class="px-4 py-2 text-center whitespace-nowrap">
                                                <span 
                                                    :class="getStatus(order.status).container" 
                                                    class="pl-2 pr-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1.5 border shadow-[0_1px_2px_rgba(0,0,0,0.02)] transition-all duration-300"
                                                >
                                                    <!-- Indicator Dot Wrapper -->
                                                    <span class="relative flex h-1.5 w-1.5">
                                                        <!-- Active/Processing Status Pings -->
                                                        <span 
                                                            v-if="['pending', 'processing', 'out for delivery'].includes(order.status.toLowerCase())"
                                                            class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                                            :class="getStatus(order.status).dot"
                                                        ></span>
                                                        <span class="relative inline-flex rounded-full h-1.5 w-1.5" :class="getStatus(order.status).dot"></span>
                                                    </span>
                                                    
                                                    <!-- Status Text -->
                                                    <span>{{ order.status }}</span>
                                                </span>
                                            </td>
                                        </tr>
                                    </template>

                                    <!-- Empty State -->
                                    <tr v-else>
                                        <td colspan="6" class="px-6 py-16 text-center">
                                            <div class="flex flex-col items-center justify-center max-w-xs mx-auto text-slate-400">
                                                <i class="fa-solid fa-inbox text-xl mb-2"></i>
                                                <p class="text-xs font-semibold tracking-wide uppercase">No orders found for today</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <div class="flex flex-col gap-2 border-slate-200 bg-white dark:bg-slate-900 shadow-sm px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                        <!-- Showing info -->
                        <p class="text-xs text-slate-500">
                            Showing
                            <span class="font-semibold text-slate-700">{{ pagination.from }}</span>
                            –
                            <span class="font-semibold text-slate-700">{{ pagination.to }}</span>
                            of
                            <span class="font-semibold text-slate-700">{{ pagination.total }}</span>
                        </p>

                        <div class="flex flex-wrap items-center justify-end gap-2">
                            <!-- First -->
                            <button
                                @click="changePage(1)" :disabled="pagination.page === 1 || loading"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40">
                                <i class="fa-solid fa-angles-left"></i>
                            </button>

                            <!-- Prev -->
                            <button
                                @click="changePage(pagination.page - 1)" :disabled="pagination.page === 1 || loading"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40">
                                <i class="fa-solid fa-chevron-left"></i>
                            </button>

                            <!-- Pages -->
                            <button
                                v-for="page in OrderVisiblePages"
                                :key="String(page)"
                                @click="page !== '...' && changePage(page)"
                                class="rounded-lg border px-3 py-1.5 text-xs font-semibold"
                                :disabled="page === '...' || loading"
                                :class="[
                                    page === pagination.page
                                        ? 'border-slate-900 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900'
                                        : 'border-slate-200 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-100 hover:bg-slate-50'
                                ]">
                                {{ page }}
                            </button>

                            <!-- Next -->
                            <button
                                @click="changePage(pagination.page + 1)"
                                :disabled="pagination.page === pagination.lastPage || loading"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40">
                                <i class="fa-solid fa-angle-right"></i>
                            </button>

                            <!-- Last -->
                            <button
                                @click="changePage(pagination.lastPage)"
                                :disabled="pagination.page === pagination.lastPage || loading"
                                class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40">
                                <i class="fa-solid fa-angles-right"></i>
                            </button>
                        </div>
                    </div>


                </main>
            </div>
        </div>
        <FooterSection />
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from "vue-router";
import api from '../../../services/api';

import Navbar from '../admin/admin-navbar.vue';
import Header from '../admin/admin-header.vue';
import Message from '../../Message/message.vue';
import FooterSection from "../../e-commerce/footer.vue";

const sidebarOpen = ref(false);
const active = ref("dashboard");

const router = useRouter();

const successMsg = ref('');
const errorMsg = ref('');
const search = ref('');
const loading = ref(false);
let timer;






// =============================
// Get orders
// =============================
const orderPage = ref(1);
const orderLastPage = ref(1);
const orderTotal = ref(0);
const orderPerPage = ref(20);
const orderFromItem = ref(0);
const orderToItem = ref(0);

const OrderVisiblePages = computed(() => {
    const pages = [];
    const last = pagination.value.lastPage;
    const cur = pagination.value.page;

    if (last <= 5) {
        for (let i = 1; i <= last; i++) pages.push(i);
        return pages;
    }

    pages.push(1);

    if (cur > 3) pages.push("...");

    const start = Math.max(2, cur - 1);
    const end = Math.min(last - 1, cur + 1);

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }

    if (cur < last - 2) pages.push("...");

    pages.push(last);

    return pages;
});



const orders = ref([]);
const searchQuery = ref('');
const statusFilter = ref('');

const pagination = ref({
    page: 1,
    lastPage: 1,
    total: 0,
    perPage: 20,
    from: 0,
    to: 0,
});

async function fetchOrders(page = 1){
    loading.value = true;
    errorMsg.value = '';

    try{
        const res = await api.get('/orders/status', {
            params: { 
                page,
                search: searchQuery.value.trim(),
                status: statusFilter.value,
            }
        });
        const response = res.data;

        orders.value = response?.data?.data ?? [];

        // PAGINATION META
        pagination.value = {
            page: response?.data?.current_page ?? 1,
            lastPage: response?.data?.last_page ?? 1,
            total: response?.data?.total ?? 0,
            perPage: response?.data?.per_page ?? 20,
            from: response?.data?.from ?? 0,
            to: response?.data?.to ?? 0,
        };
        // console.log(orders.value);
    } catch(err){
        errorMsg.value = err || "Something is wrong to fetched orders.";
        console.log(err);

        orders.value = [];

        pagination.value = {
            page: 1,
            lastPage: 1,
            total: 0,
            perPage: 20,
            from: 0,
            to: 0,
        };
    } finally {
        loading.value = false;
    }
}

const formatDate = (date) => new Date(date).toLocaleDateString('en-US', { day: 'numeric', month: 'short', year: 'numeric' });

const statusConfig = {
    'pending': {
        container: 'bg-amber-50 text-amber-700 border-amber-200/70 dark:bg-amber-500/5 dark:text-amber-400 dark:border-amber-500/20',
        dot: 'bg-amber-500'
    },
    'confirmed': {
        container: 'bg-sky-50 text-sky-700 border-sky-200/70 dark:bg-sky-500/5 dark:text-sky-400 dark:border-sky-500/20',
        dot: 'bg-sky-500'
    },
    'processing': {
        container: 'bg-indigo-50 text-indigo-700 border-indigo-200/70 dark:bg-indigo-500/5 dark:text-indigo-400 dark:border-indigo-500/20',
        dot: 'bg-indigo-500'
    },
    'picked': {
        container: 'bg-fuchsia-50 text-fuchsia-700 border-fuchsia-200/70 dark:bg-fuchsia-500/5 dark:text-fuchsia-400 dark:border-fuchsia-500/20',
        dot: 'bg-fuchsia-500'
    },
    'shipped': {
        container: 'bg-blue-50 text-blue-700 border-blue-200/70 dark:bg-blue-500/5 dark:text-blue-400 dark:border-blue-500/20',
        dot: 'bg-blue-500'
    },
    'out for delivery': {
        container: 'bg-orange-50 text-orange-700 border-orange-200/70 dark:bg-orange-500/5 dark:text-orange-400 dark:border-orange-500/20',
        dot: 'bg-orange-500'
    },
    'delivered': {
        container: 'bg-emerald-50 text-emerald-700 border-emerald-200/70 dark:bg-emerald-500/5 dark:text-emerald-400 dark:border-emerald-500/20',
        dot: 'bg-emerald-500'
    },
    'cancelled': {
        container: 'bg-rose-50 text-rose-700 border-rose-100 dark:bg-rose-500/5 dark:text-rose-400 dark:border-rose-500/10',
        dot: 'bg-rose-400'
    },
    'failed': {
        container: 'bg-red-50 text-red-700 border-red-100 dark:bg-red-500/5 dark:text-red-400 dark:border-red-500/10',
        dot: 'bg-red-500'
    },
    'returned': {
        container: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700/60',
        dot: 'bg-slate-400'
    },
    'default': {
        container: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700/60',
        dot: 'bg-slate-400'
    }
};

const getStatus = (status) => {
    if (!status) return statusConfig.default;
    return statusConfig[status.toLowerCase()] || statusConfig.default;
};

// =============================
// Filter orders
// =============================

let searchTimeout = null;
watch(searchQuery, () => {
    clearTimeout(timer);

    timer = setTimeout(() => {
        fetchOrders(1);
    }, 500);
});

watch(statusFilter, () => {
    fetchOrders(1);
});

const resetFilters = () => {
    searchQuery.value = '';
    statusFilter.value = '';
    fetchOrders(1);
};

async function changePage(page) {
    await fetchOrders(page);
}







function viewOrderDetails(order){
    router.push(`/admin/orders/${order.reg}/${order.slug}`);
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

const onSearch = () => {
    console.log(search.value)
}

/* ESC to close drawer */
onMounted(() => {
    fetchOrders();


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