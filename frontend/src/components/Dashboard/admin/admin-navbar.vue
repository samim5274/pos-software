<template>
    <!-- Overlay (mobile) -->
    <div
        class="fixed inset-0 z-40 lg:hidden bg-black/40 dark:bg-black/60"
        v-show="mobileMenu"
        @click="$emit('close')"
    />

    <aside
        class="fixed inset-y-0 left-0 z-50 w-72
        border-r transform transition-transform duration-200
        lg:static lg:translate-x-0 lg:h-auto
        overflow-y-auto
        bg-white text-slate-900 border-slate-200
        dark:bg-slate-900 dark:text-slate-100 dark:border-white/10"
        :class="mobileMenu ? 'translate-x-0' : '-translate-x-full'">
        

        <!-- Menu -->
        <nav class="p-3">
            <ul class="space-y-1">

                <!-- Dashboard -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="activeKey === 'dashboard'
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-white/10 dark:text-white dark:ring-white/10'
                        : ''"
                        @click="pick('dashboard')">
                        <span class="opacity-90 w-5 text-center">
                        <i class="fa-solid fa-house"></i>
                        </span>
                        <span class="text-sm font-medium">Dashboard</span>
                    </button>
                </li>



                <!-- Profile -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="activeKey === 'profile'
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-white/10 dark:text-white dark:ring-white/10'
                        : ''"
                        @click="pick('profile')">
                        <span class="opacity-90 w-5 text-center">
                        <i class="fa-regular fa-circle-user"></i>
                        </span>
                        <span class="text-sm font-medium">Profile</span>
                    </button>
                </li>

                <!-- Cart -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="activeKey === 'cart'
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-white/10 dark:text-white dark:ring-white/10'
                        : ''"
                        @click="pick('cart')">
                        <span class="opacity-90 w-5 text-center">
                        <i class="fa-solid fa-cart-plus"></i>
                        </span>
                        <span class="text-sm font-medium">Cart</span>
                    </button>
                </li>

                <!-- Product Details Dropdown -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="pagesOpen
                        ? 'bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10'
                        : ''"
                        @click="pagesOpen = !pagesOpen"
                        type="button">
                        <span class="opacity-90">
                        <i class="fa-regular fa-folder-open"></i>
                        </span>
                        <span class="text-sm font-medium flex-1">Product Details</span>

                        <svg
                        class="h-4 w-4 transition-transform opacity-80"
                        :class="pagesOpen ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="pagesOpen" class="mt-1 ml-6 mr-2 rounded-xl ring-1
                            bg-slate-50 ring-slate-200
                            dark:bg-black/20 dark:ring-white/10">
                        <ul class="py-2">
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'products'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('products')">
                                Product List
                                </button>
                            </li>

                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'create'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('create')">
                                Create Product
                                </button>
                            </li>

                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'product_setting'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('product_setting')">
                                Settings
                                </button>
                            </li>
                        </ul>
                    </div>
                </li>





                <!-- Product stock -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="stockPagesOpen
                        ? 'bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10'
                        : ''"
                        @click="stockPagesOpen = !stockPagesOpen"
                        type="button">
                        <span class="opacity-90">
                        <i class="fa-solid fa-warehouse"></i>
                        </span>
                        <span class="text-sm font-medium flex-1">Stock</span>

                        <svg
                        class="h-4 w-4 transition-transform opacity-80"
                        :class="stockPagesOpen ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="stockPagesOpen" class="mt-1 ml-6 mr-2 rounded-xl ring-1
                            bg-slate-50 ring-slate-200
                            dark:bg-black/20 dark:ring-white/10">
                        <ul class="py-2">
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'product_stock'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('product_stock')">
                                Stock In
                                </button>
                            </li>
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'purchase_product'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('purchase_product')">
                                Purchase Stock In
                                </button>
                            </li>

                            <hr class="border-0 border-t border-slate-400 dark:border-slate-700">
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'report_stock'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('report_stock')">
                                Stock Report's
                                </button>
                            </li>
                        </ul>
                    </div>
                </li>






                <!-- Order -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="orderPagesOpen
                        ? 'bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10'
                        : ''"
                        @click="orderPagesOpen = !orderPagesOpen"
                        type="button">
                        <span class="opacity-90">
                        <i class="fa-solid fa-check-to-slot"></i>
                        </span>
                        <span class="text-sm font-medium flex-1">Order</span>

                        <svg
                        class="h-4 w-4 transition-transform opacity-80"
                        :class="orderPagesOpen ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="orderPagesOpen" class="mt-1 ml-6 mr-2 rounded-xl ring-1
                            bg-slate-50 ring-slate-200
                            dark:bg-black/20 dark:ring-white/10">
                        <ul class="py-2">
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'orders'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('orders')">
                                Orders
                                </button>
                            </li>

                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'order_payment'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('order_payment')">
                                Payment
                                </button>
                            </li>
                        </ul>
                    </div>
                </li>










                <!-- Order -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="reportPagesOpen
                        ? 'bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10'
                        : ''"
                        @click="reportPagesOpen = !reportPagesOpen"
                        type="button">
                        <span class="opacity-90">
                        <i class="fa-solid fa-sliders"></i>
                        </span>
                        <span class="text-sm font-medium flex-1">Reports</span>

                        <svg
                        class="h-4 w-4 transition-transform opacity-80"
                        :class="reportPagesOpen ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="reportPagesOpen" class="mt-1 ml-6 mr-2 rounded-xl ring-1
                            bg-slate-50 ring-slate-200
                            dark:bg-black/20 dark:ring-white/10">
                        <ul class="py-2">
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'report_sale'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('report_sale')">
                                Sale Report's
                                </button>
                            </li>

                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'report_payment'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('report_payment')">
                                Payment Report's
                                </button>
                            </li>
                        </ul>
                    </div>
                </li>








                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="expensesPagesOpen
                        ? 'bg-slate-100 ring-1 ring-slate-200 dark:bg-white/10 dark:ring-white/10'
                        : ''"
                        @click="expensesPagesOpen = !expensesPagesOpen"
                        type="button">
                        <span class="opacity-90">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                        </span>
                        <span class="text-sm font-medium flex-1">Expenses</span>

                        <svg
                        class="h-4 w-4 transition-transform opacity-80"
                        :class="expensesPagesOpen ? 'rotate-180' : ''"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <div v-show="expensesPagesOpen" class="mt-1 ml-6 mr-2 rounded-xl ring-1
                            bg-slate-50 ring-slate-200
                            dark:bg-black/20 dark:ring-white/10">
                        <ul class="py-2">
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'expenses'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('expenses')">
                                Expenses
                                </button>
                            </li>
                            
                            <li>
                                <button
                                class="w-full px-4 py-2 text-sm text-left transition
                                        hover:bg-white dark:hover:bg-white/10"
                                :class="activeKey === 'SettingExpenses'
                                    ? 'bg-white text-slate-900 font-medium dark:bg-white/10 dark:text-white'
                                    : 'text-slate-700 dark:text-slate-200/90'"
                                @click="pick('SettingExpenses')">
                                Setting
                                </button>
                            </li>
                        </ul>
                    </div>
                </li>




                <!-- Notice -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="activeKey === 'notice'
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-white/10 dark:text-white dark:ring-white/10'
                        : ''"
                        @click="pick('notice')">
                        <span class="opacity-90 w-5 text-center">
                        <i class="fa-solid fa-bell"></i>
                        </span>
                        <span class="text-sm font-medium">Notice</span>
                    </button>
                </li>







                <!-- Setting -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="activeKey === 'setting'
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-white/10 dark:text-white dark:ring-white/10'
                        : ''"
                        @click="pick('setting')">
                        <span class="opacity-90 w-5 text-center">
                        <i class="fa-solid fa-gear"></i>
                        </span>
                        <span class="text-sm font-medium">Setting</span>
                    </button>
                </li>



                <!-- Logout -->
                <li>
                    <button
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-left transition
                            focus:outline-none focus:ring-2 focus:ring-slate-500/40
                            hover:bg-slate-100 dark:hover:bg-white/10"
                        :class="activeKey === 'logout'
                        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 dark:bg-white/10 dark:text-white dark:ring-white/10'
                        : ''"
                        @click="pick('logout')">
                        <span class="opacity-90 w-5 text-center">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        </span>
                        <span class="text-sm font-medium">Logout</span>
                    </button>
                </li>


            </ul>
        </nav>
    </aside>
</template>

<script setup>
import { computed, onMounted, ref, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import api from '../../../services/api';
import { useAuth } from '../../../stores/auth';

const { authUser, loadUser, logout: authLogout } = useAuth();

const props = defineProps({
    mobileMenu: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["close", "update:modelValue", "navigate"]);

const route = useRoute();
const router = useRouter();

const pagesOpen = ref(false);
const stockPagesOpen = ref(false);
const userPagesOpen = ref(false);
const orderPagesOpen = ref(false);
const reportPagesOpen = ref(false);
const expensesPagesOpen = ref(false);










const routeMap = {
    dashboard: "/admin/dashboard",
    profile: "/admin/profile",
    cart: "/sale-cart",

    products: "/admin/products",
    create: "/admin/create-product",
    product_setting: "/admin/product/setting",

    product_stock: "/admin/product/stock",
    purchase_product: "/admin/product/purchase/stock",
    report_stock: "/admin/reports/stock",

    orders: "/admin/orders",
    order_payment: "/admin/orders/payment",

    // reports
    report_sale: "/admin/reports/sale",
    report_payment: "/admin/reports/payment",    


    // Others
    notice: "/admin/notice",
    expenses: "/admin/expenses",
    SettingExpenses: "/admin/expenses/setting",

    setting: "/admin/setting",
    logout: "/login",
};














const routeMatch = [
    { key: "dashboard", prefixes: ["/admin/dashboard"] },
    { key: "profile", prefixes: ["/admin/profile"] },
    { key: "cart", prefixes: ["/sale-cart"] },

    { key: "setting", prefixes: ["/admin/setting"] },

    { key: "products", prefixes: ["/admin/products", "/admin/product-edit"] },
    { key: "create", prefixes: ["/admin/create-product"] },
    { key: "product_setting", prefixes: ["/admin/product/setting"] },

    { key: "product_stock", prefixes: ["/admin/product/stock"] },
    { key: "purchase_product", prefixes: ["/admin/product/purchase/stock"] },
    { key: "report_stock", prefixes: ["/admin/reports/stock"] },

    { key: "order_payment", prefixes: ["/admin/orders/payment"] },
    { key: "orders", prefixes: ["/admin/orders", "/admin/customer-details/"] },

    { key: "report_sale", prefixes: ["/admin/reports/sale"] },
    { key: "report_payment", prefixes: ["/admin/reports/payment"] },    


    { key: "notice", prefixes: ["/admin/notice"] },
    { key: "SettingExpenses", prefixes: ["/admin/expenses/setting"] },
    { key: "expenses", prefixes: ["/admin/expenses"] },

    { key: "logout", prefixes: ["/logout"] },
];












const activeKey = computed(() => {
    const path = route.path;
    const hit = routeMatch.find((r) =>
        r.prefixes.some((pre) => path.startsWith(pre))
    );
    return hit?.key ?? "dashboard";
});

async function pick(key) {
    emit("update:modelValue", key);
    emit("navigate", key);

    if (key === "logout") {
        try {
        await api.post("/auth/logout");
        } catch (e) {
        // ignore
        } finally {
        authLogout();
        emit("close");
        return router.push("/login");
        }
    }

    const to = routeMap[key];
    if (to && route.path !== to) {
        await router.push(to);
    }

    emit("close");
}











// product page open close
watch(
    () => activeKey.value,
    (k) => {
        const productKeys = ["products", "create","product_setting"];
        if (productKeys.includes(k)) {
        pagesOpen.value = true;
        }
    },
    { immediate: true }
);







// Stock page open close
watch(
    () => activeKey.value,
    (k) => {
        const stockPageKey = ["product_stock", "report_stock", "purchase_product"];
        if (stockPageKey.includes(k)) {
        stockPagesOpen.value = true;
        }
    },
    { immediate: true }
);












// Expenses dropdown
watch(
    () => activeKey.value,
    (k) => {
        const expensesKeys = ['expenses', 'SettingExpenses'];
        if(expensesKeys.includes(k)){
        expensesPagesOpen.value = true;
        }
    },
    {immediate: true }
);

// watch(
//     () => route.path,
//     (path) => {
//         console.log('Current Path:', path);
//         console.log('Active Key:', activeKey.value);
//     },
//     { immediate: true }
// );








// Order page open close
watch(
    () => activeKey.value,
    (k) => {
        const orderKeys = ["orders", "order_payment", "sales"];
        if (orderKeys.includes(k)) {
        orderPagesOpen.value = true;
        }
    },
    { immediate: true }
);












// Report page open close
watch(
    () => activeKey.value,
    (k) => {
        const reportKeys = [
        "report_sale", 
        "report_payment", 
        ];
        if (reportKeys.includes(k)) {
        reportPagesOpen.value = true;
        }
    },
    { immediate: true }
);














onMounted(() => {
    loadUser();
})

</script>