import { createRouter, createWebHistory } from 'vue-router';

// Auth Routes
const Login = () => import('../components/Auth/login.vue');
const Register = () => import('../components/Auth/register.vue');
const SocialLogin = () => import('../components/Auth/SocialCallback.vue');
const FindAccount = () => import('../components/Auth/find-account.vue');
const otpVerification = () => import('../components/Auth/otp-verification.vue');
const resetPassword = () => import('../components/Auth/reset-password.vue');
const Unauthorized = () => import('../components/Auth/unauthorized.vue');






const AdminDashboard = () => import('../components/Dashboard/admin/admin-dashboard.vue');
const AdminProfile = () => import('../components/Dashboard/admin/admin-profile.vue');


// product import
const AdminProductList = () => import('../components/Dashboard/admin/product/product-list.vue');
const AdminCreateProduct = () => import('../components/Dashboard/admin/product/create-product.vue');
const AdminProductEdit = () => import('../components/Dashboard/admin/product/product-edit.vue');
const AdminProductSetting = () => import('../components/Dashboard/admin/product/product-setting.vue');




// Product Stock
const ProductStock = () => import('../components/Dashboard/admin/stock/product-stock.vue');
const StockReport = () => import('../components/Dashboard/admin/stock/report-stock.vue');
const StockReportPrint = () => import('../components/Dashboard/admin/stock/stock-report-print.vue');
const PurchaseProductStock = () => import('../components/Dashboard/admin/stock/purchase-product.vue');








const SaleCart = () => import('../components/Dashboard/admin/cart/cart.vue');
const OrderList = () => import('../components/Dashboard/admin/order/order.vue');
const OrderDetails = () => import('../components/Dashboard/admin/order/order-details.vue');
const PrintInvoice = () => import('../components/Dashboard/admin/order/print/print-order-invoice-80mm.vue');
const OrderPayment = () => import('../components/Dashboard/admin/order/order-payment.vue');







const SaleReport = () => import('../components/Dashboard/admin/reports/report-sale.vue');
const PaymentReport = () => import('../components/Dashboard/admin/reports/report-payment.vue');








const C_Notice = () => import('../components/Dashboard/admin/notice/notice.vue');
const C_Notice_details = () => import('../components/Dashboard/admin/notice/customer-notice-details.vue');










const Expenses = () => import('../components/Dashboard/admin/Expense/expenses.vue');
const ExpensesPrint = () => import('../components/Dashboard/admin/Expense/print-expenses.vue')
const ExpensesDetails = () => import('../components/Dashboard/admin/Expense/expenses-details.vue')
const ExpensesSetting = () => import('../components/Dashboard/admin/Expense/expenses-setting.vue')











const routes = [
    // Auth Routes
    { path: '/login', component: Login, meta: {title: "Login - Mercuviax - Pos Software | Bangladesh's Best POS Software Company"} },
    { path: '/register/:refer_code?', component: Register, meta: {title: "Register - Mercuviax - Pos Software | Bangladesh's Best POS Software Company"} },
    { path: '/forget-password', component: FindAccount, meta: {title: "Forget Password - Mercuviax - Pos Software | Bangladesh's Best POS Software Company"} },
    { path: '/otp-verification', component: otpVerification, meta: {title: "OTP Verification - Mercuviax - Pos Software | Bangladesh's Best POS Software Company", requiresEmail: true} },
    { path: '/reset-password', component: resetPassword, meta: {title: "Reset Password - Mercuviax - Pos Software | Bangladesh's Best POS Software Company", requiresEmail: true} },
    { path: '/unauthorized', component: Unauthorized, meta: {title: "Unauthorized - Mercuviax - Pos Software | Bangladesh's Best POS Software Company"} },
    { path: "/auth/social", component: SocialLogin, meta: { title: "Social Login - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},











    // Admin Route 
    { path: "/", component: AdminDashboard, meta: { title: "Dashboard - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},

    { path: "/admin/dashboard", component: AdminDashboard, meta: { requiresAuth: true, roles: ['admin', 'super_admin'], title: "Admin Dashboard - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},
    { path: "/admin/profile", component: AdminProfile, meta: { requiresAuth: true, roles: ['admin', 'super_admin'], title: "Profile - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},
    

    // Product routes
    { path: "/admin/products", component: AdminProductList, meta: { requiresAuth: true, roles: ['admin', 'super_admin'], title: "Products List - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},
    { path: "/admin/create-product", component: AdminCreateProduct, meta: { requiresAuth: true, roles: ['admin', 'super_admin'], title: "Create Product - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},
    { path: "/admin/product/setting", component: AdminProductSetting, meta: { requiresAuth: true, roles: ['admin', 'super_admin'], title: "Product Settings - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" }},
    { path: '/admin/product-edit/:slug', component: AdminProductEdit, meta: { requiresAuth: true, roles: ['admin', 'super_admin'], title: "Edit Product - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    
    
    
    
    
    // Product stock
    { path: '/admin/product/stock', component: ProductStock, meta: { requiresAuth: true, roles: ['staff', 'admin', 'super_admin'], title: "Product Stock - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    { path: '/admin/reports/stock', component: StockReport, meta: { requiresAuth: true, roles: ['staff', 'admin', 'super_admin'], title: "Stock Report's - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    { path: '/admin/stock-report-print', component: StockReportPrint, meta: { requiresAuth: true, roles: ['staff', 'admin', 'super_admin'], title: "Stock Report Print's - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    { path: '/admin/product/purchase/stock', component: PurchaseProductStock, meta: { requiresAuth: true, roles: ['staff', 'admin', 'super_admin'], title: "Purchase product stocks - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    
    
    
    
    
    
    
    
    // Sale Cart
    { path: '/sale-cart', component: SaleCart, meta: { requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Sale Cart - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    { path: '/admin/orders', component: OrderList, meta: { requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Order List - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    { path: '/admin/orders/:reg/:slug', component: OrderDetails, meta: { requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Order Details - Mercuviax - Pos Software | Bangladesh's Best POS Software Company" } },
    // Print invoice
    { path: '/admin/order/invoice-print/:reg', component: PrintInvoice, meta: {requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Print Order Details"}},
    { path: '/admin/orders/payment', component: OrderPayment, meta: {requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Order Payment Details"}},

    // Sale Report
    { path: '/admin/reports/sale', component: SaleReport, meta: {requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Sale Reports"}},
    { path: '/admin/reports/payment', component: PaymentReport, meta: {requiresAuth: true, roles: ['admin', 'super_admin', 'staff'], title: "Payments Reports"}},










    { path: '/admin/notice', component: C_Notice, meta:{title: 'Notice', requiresAuth: true, roles: ['staff', 'admin', 'super_admin']}},
    { path: '/admin/notice/:id/details', component: C_Notice_details, meta:{title: 'Notice Details', requiresAuth: true, roles: ['staff', 'admin', 'super_admin']}},
    
    
    
    
    
    
    
    
    
    
    { path: '/admin/expenses', component: Expenses, meta:{title: 'Expenses', requiresAuth: true, roles: ['staff', 'admin', 'super_admin']}},
    { path: '/admin/expenses/print/:id', component: ExpensesPrint, meta:{title: 'Expenses Print', requiresAuth: true, roles: ['staff', 'admin', 'super_admin']}},
    { path: '/admin/expenses/details/:id', component: ExpensesDetails, meta:{title: 'Expenses Details', requiresAuth: true, roles: ['staff', 'admin', 'super_admin']}},
    { path: '/admin/expenses/setting', component: ExpensesSetting, meta:{title: 'Expenses Setting', requiresAuth: true, roles: ['staff', 'admin', 'super_admin']}},

    
]












const router = createRouter({
    history: createWebHistory(import.meta.env.VITE_BASE_URL || '/'),
    routes,

    scrollBehavior(to, from, savedPosition) {
        if (savedPosition) {
            return savedPosition;
        }

        return {
            top: 0,
            behavior: "smooth",
        };
    },
});








// security check
router.beforeEach((to, from, next) => {
  
    const token = localStorage.getItem("token");
    const user = JSON.parse(localStorage.getItem('user'));

    // Set page title
    if (to.meta.title) {
        document.title = to.meta.title;
    }

    // Check if route requires email
    if (to.meta.requiresEmail) {
        const email = localStorage.getItem('email')
        if (!email) {
        // Email not found, redirect to forget-password
        return next('/forget-password');
        }
    }

    if (to.meta.requiresAuth && !token) {
        return next('/login');
    }

    // console.log(to.meta.roles);
    if (to.meta.roles) {
        if (!user) {
        return next('/login');
        }

        if (!to.meta.roles.includes(user.role)) {
        return next('/unauthorized');
        }
    }

    next();
});

export default router