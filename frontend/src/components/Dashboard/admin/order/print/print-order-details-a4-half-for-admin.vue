<template>
    <!-- Print Wrapper -->
    <div class="a4-page">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <h1>Ogrova Business</h1>
                <p>Khilkhet, Dhaka-1229, Bangladesh</p>
                <p class="meta">
                    ogrova2026@gmail.com &nbsp;|&nbsp; +880 1533-021557 &nbsp;|&nbsp; www.ogrova.com
                </p>
            </div>
            <div class="header-right" v-if="order">
                <h2>INVOICE</h2>
                <p class="inv-no">#{{ order.reg ?? 'N/A' }}</p>
                <p class="inv-date">{{ formatDate(order.date) }}</p>
            </div>
        </div>

        <hr class="divider" />

        <!-- Order + Customer Information -->
        <div class="two-col" v-if="order">
            <div class="card">
                <div class="card-header">Order Information</div>
                <div class="card-body">
                    <div class="row"><span class="label">Order No</span><span class="value">{{ order.reg ?? 'N/A' }}</span></div>
                    <div class="row"><span class="label">Order Date</span><span class="value">{{ formatDate(order.date) }}</span></div>
                    <div class="row"><span class="label">Status</span><span class="value">{{ order.status ?? 'N/A' }}</span></div>
                    <div class="row"><span class="label">Payment Status</span><span class="value">{{ order.payment_status ?? 'N/A' }}</span></div>
                    <div class="row"><span class="label">Payment Method</span><span class="value">{{ getPaymentMethod(order.payment_method)?.label ?? 'N/A' }}</span></div>
                    <div class="row"><span class="label">Point Earn</span><span class="value">{{ order.point ?? 0 }}</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">Customer Information</div>
                <div class="card-body">
                    <div class="row"><span class="label">Customer</span><span class="value">{{ order.contact_name ?? '-' }}</span></div>
                    <div class="row"><span class="label">Phone</span><span class="value">{{ order.contact_number ?? '-' }}</span></div>
                    <div class="row"><span class="label">Email</span><span class="value">{{ order.contact_email ?? '-' }}</span></div>
                    <div class="row"><span class="label">Address</span><span class="value">{{ order.shipping_address ?? '-' }}</span></div>
                    <div class="row"><span class="label">Area</span><span class="value">{{ [order.upazila?.name, order.district?.name, order.division?.name].filter(Boolean).join(', ') || '-' }}</span></div>
                    <div class="row"><span class="label">Postal Code</span><span class="value">{{ order.postal_code ?? '-' }}</span></div>
                </div>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="items-section" v-if="cartItems && cartItems.length">
            <div class="section-title">Order Items</div>

            <table class="items-table">
                <thead>
                    <tr>
                        <th class="col-sl">#</th>
                        <th class="col-item">Item Description</th>
                        <th class="col-qty text-center">Qty</th>
                        <th class="col-price text-right">Unit Price</th>
                        <th class="col-discount text-right">Discount</th>
                        <th class="col-total text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, idx) in cartItems" :key="item.id ?? idx">
                        <td class="col-sl">{{ idx + 1 }}</td>
                        <td class="col-item">
                            <div class="item-name">{{ item.product?.name ?? 'Product' }}</div>
                            <div v-if="item.variant?.name" class="item-variant">
                                Variant: {{ item.variant.name }}
                            </div>
                            <div v-if="item.note" class="item-note">
                                Note: {{ item.note }}
                            </div>
                        </td>
                        <td class="col-qty text-center">{{ item.quantity ?? 1 }}</td>
                        <td class="col-price text-right">৳ {{ formatMoney(item.price) }}</td>
                        <td class="col-discount text-right">
                            <span v-if="item.discount > 0" class="text-red">- ৳ {{ formatMoney(item.discount) }}</span>
                            <span v-else>-</span>
                        </td>
                        <td class="col-total text-right">
                            ৳ {{ formatMoney(item.payable_amount ?? ((item.price - (item.discount ?? 0)) * (item.quantity ?? 1))) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5" class="text-right foot-label">Total Items: {{ cartItems.length }} &nbsp;•&nbsp; Total Qty: {{ cartItems.reduce((s, i) => s + Number(i.quantity ?? 1), 0) }}</td>
                        <td class="text-right foot-value">
                            ৳ {{ formatMoney(cartItems.reduce((sum, item) => sum + Number(item.payable_amount ?? ((item.price - (item.discount ?? 0)) * (item.quantity ?? 1))), 0)) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="bottom-grid">
            <!-- Left column: Payment + Delivery -->
            <div class="bottom-left">
                <div class="card" v-if="payment">
                    <div class="card-header">Payment Information</div>
                    <div class="card-body">
                        <div class="row"><span class="label">Receipt</span><span class="value">{{ payment.receipt_no ?? '-' }}</span></div>
                        <div class="row"><span class="label">Method</span><span class="value">{{ getPaymentMethod(payment.payment_method)?.label ?? '-' }}</span></div>
                        <div class="row"><span class="label">Type</span><span class="value">{{ payment.payment_type ?? '-' }}</span></div>
                        <div class="row"><span class="label">Status</span><span class="value">{{ payment.status ?? '-' }}</span></div>
                        <div class="row"><span class="label">Paid Amount</span><span class="value highlight-green">৳ {{ formatMoney(payment.amount) }}</span></div>
                        <div class="row"><span class="label">Paid At</span><span class="value">{{ formatDate(payment.paid_at) }}</span></div>
                        <div class="row"><span class="label">Verified By</span><span class="value">{{ payment.verifier?.name ?? '-' }}</span></div>
                        <div class="row"><span class="label">Verified At</span><span class="value">{{ formatDate(payment.verified_at) }}</span></div>
                    </div>
                </div>

                <div class="card mt-gap" v-if="deliveryCharge">
                    <div class="card-header">Delivery Charge</div>
                    <div class="card-body">
                        <div class="row"><span class="label">Amount</span><span class="value highlight-green">৳ {{ formatMoney(deliveryCharge.amount) }}</span></div>
                        <div class="row"><span class="label">Method</span><span class="value">{{ getPaymentMethod(deliveryCharge.payment_method)?.label ?? '-' }}</span></div>
                        <div class="row"><span class="label">Status</span><span class="value">{{ deliveryCharge.payment_status ?? '-' }}</span></div>
                        <div class="row"><span class="label">Paid By</span><span class="value">{{ deliveryCharge.paid_by?.name || '-' }}</span></div>
                        <div class="row" v-if="deliveryCharge.notes"><span class="label">Notes</span><span class="value">{{ deliveryCharge.notes }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Right column: Order Summary -->
            <div class="bottom-right" v-if="order">
                <div class="card">
                    <div class="card-header">Order Summary</div>
                    <div class="card-body">
                        <table class="summary-table">
                            <tr>
                                <td>Subtotal</td>
                                <td class="text-right">৳ {{ formatMoney(order.amount) }}</td>
                            </tr>
                            <tr>
                                <td>Discount</td>
                                <td class="text-right text-red">- ৳ {{ formatMoney(order.discount) }}</td>
                            </tr>
                            <tr>
                                <td>Shipping Charge</td>
                                <td class="text-right">৳ {{ formatMoney(order.shipping_charge) }}</td>
                            </tr>
                            <tr>
                                <td>Coupon Discount</td>
                                <td class="text-right text-red">- ৳ {{ formatMoney(order.coupon_discount) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>Total Payable</td>
                                <td class="text-right">৳ {{ formatMoney(order.payable_amount) }}</td>
                            </tr>
                            <tr>
                                <td>Paid</td>
                                <td class="text-right text-green">৳ {{ formatMoney(order.paid_amount) }}</td>
                            </tr>
                            <tr class="due-row">
                                <td>Due</td>
                                <td class="text-right text-red">৳ {{ formatMoney(order.due_amount) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signatures -->
        <div class="signatures">
            <div class="sig">
                <div class="sig-line"></div>
                <span>Prepared By</span>
            </div>
            <div class="sig">
                <div class="sig-line"></div>
                <span>Customer Signature</span>
            </div>
            <div class="sig">
                <div class="sig-line"></div>
                <span>Approved By</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            Thank you for your business! &nbsp;•&nbsp; Powered by <strong>Mercuviax</strong> &nbsp;|&nbsp; +880 1533-021557
        </div>

        <!-- Debug -->
        <div v-if="orderLoading" class="debug">Loading...</div>
        <div v-if="cartLoading" class="debug">Loading...</div>
        <div v-if="errorMsg" class="debug error">{{ errorMsg }}</div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { useRoute } from "vue-router";
import api from '../../../../services/api';
import { UAParser } from 'ua-parser-js';

const route = useRoute();

const orderLoading = ref(false);
const cartLoading = ref(false);
const errorMsg = ref('');

// ==========================================
// 1. Reactive State
// ==========================================
const order = ref(null);
const payments = ref([]);
const payment = ref(null);
const deliveryCharge = ref(null);
const cartItems = ref([]);

// ==========================================
// 2. Helper Functions (Formatters)
// ==========================================
const formatMoney = (amount) => {
    if (amount === null || amount === undefined || isNaN(Number(amount))) return '0.00';
    return Number(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
};

const formatDate = (date) => {
    if (!date) return "-";
    return new Date(date).toLocaleDateString(
        "en-US",
        {
            day: "numeric",
            month: "short",
            year: "numeric",
        }
    );
};

// ==========================================
// 3. API Data Fetching Functions
// ==========================================
async function fetchOrderDetails() {
    orderLoading.value = true;
    try {
        const reg = route.params.reg;
        if (!reg) {
            errorMsg.value = "Invalid order reference.";
            return;
        }

        const res = await api.get(`/orders/${route.params.reg}`);
        const responseData = res.data?.data || {};

        order.value = responseData.order || null;

        const rawPayment = responseData.payment || responseData.order?.payment;
        if (Array.isArray(rawPayment)) {
            payments.value = rawPayment;
            payment.value = rawPayment[0] || null;
        } else if (rawPayment) {
            payments.value = [rawPayment];
            payment.value = rawPayment;
        } else {
            payments.value = [];
            payment.value = null;
        }

        deliveryCharge.value = responseData.deliveryCharge || null;

        // console.log("Order details response:", responseData);
    } catch (err) {
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Something went wrong while fetching order.";
    } finally {
        orderLoading.value = false;
    }
}

async function getCartItems() {
    cartLoading.value = true;
    try {
        const res = await api.get(`/cart/${route.params.reg}`);
        cartItems.value = res.data?.data || [];
        // console.log(cartItems.value)
    } catch (err) {
        console.error(err);
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Something went wrong while fetching cart items.";
    } finally {
        cartLoading.value = false;
    }
}

// ==========================================
// 4. UI/Style Configurations & Mappings
// ==========================================

const statusConfig = {
    'Pending': { container: 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400', dot: 'bg-amber-500' },
    'Confirmed': { container: 'bg-sky-100 text-sky-700 dark:bg-sky-500/10 dark:text-sky-400', dot: 'bg-sky-500' },
    'Processing': { container: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400', dot: 'bg-indigo-500' },
    'Picked': { container: 'bg-violet-100 text-violet-700 dark:bg-violet-500/10 dark:text-violet-400', dot: 'bg-violet-500' },
    'Shipped': { container: 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400', dot: 'bg-blue-500' },
    'Out for Delivery': { container: 'bg-orange-100 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400', dot: 'bg-orange-500' },
    'Delivered': { container: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400', dot: 'bg-emerald-500' },
    'Cancelled': { container: 'bg-rose-100 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400', dot: 'bg-rose-500' },
    'Failed': { container: 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400', dot: 'bg-red-600' },
    'Returned': { container: 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-400', dot: 'bg-slate-500' },
    default: { container: 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400', dot: 'bg-gray-500' }
};

const getStatus = (status) => {
    if (!status) return statusConfig.default;
    const matchedKey = Object.keys(statusConfig).find(
        key => key.toLowerCase() === status.toLowerCase()
    );
    return statusConfig[matchedKey] || statusConfig.default;
};

function statusStyle(status) {
    const map = {
        Pending: { badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400', icon: 'fa-regular fa-clock' },
        Partial: { badge: 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400', icon: 'fa-solid fa-circle-half-stroke' },
        Paid: { badge: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400', icon: 'fa-regular fa-circle-check' },
        Failed: { badge: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400', icon: 'fa-regular fa-circle-xmark' },
        Refunded: { badge: 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400', icon: 'fa-solid fa-rotate-left' },
    };
    return map[status] || map.Pending;
}

function getPaymentMethod(method) {
    const map = {
        cod: { label: 'Cash on delivery', icon: 'fa-solid fa-money-bill-wave', iconBg: 'bg-slate-100 dark:bg-slate-500/10', iconColor: 'text-slate-500 dark:text-slate-400' },
        cash: { label: 'Cash', icon: 'fa-solid fa-money-bill-1-wave', iconBg: 'bg-slate-100 dark:bg-slate-500/10', iconColor: 'text-slate-500 dark:text-slate-400' },
        bank_transfer: { label: 'Bank transfer', icon: 'fa-solid fa-building-columns', iconBg: 'bg-amber-50 dark:bg-amber-500/10', iconColor: 'text-amber-600 dark:text-amber-400' },
        mobile_banking: { label: 'Mobile banking', icon: 'fa-solid fa-mobile-screen-button', iconBg: 'bg-emerald-50 dark:bg-emerald-500/10', iconColor: 'text-emerald-600 dark:text-emerald-400' },
        card: { label: 'Card', icon: 'fa-regular fa-credit-card', iconBg: 'bg-indigo-50 dark:bg-indigo-500/10', iconColor: 'text-indigo-600 dark:text-indigo-400' },
        paypal: { label: 'PayPal', icon: 'fa-brands fa-paypal', iconBg: 'bg-blue-50 dark:bg-blue-500/10', iconColor: 'text-blue-600 dark:text-blue-400' },
        wallet: { label: 'Wallet', icon: 'fa-solid fa-wallet', iconBg: 'bg-purple-50 dark:bg-purple-500/10', iconColor: 'text-purple-600 dark:text-purple-400' },
    };
    return map[method?.toLowerCase()] || map.cod;
}

function getPaymentStatus(status) {
    const map = {
        Pending: { badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-600 dark:text-slate-400', dot: 'bg-slate-400', accentBar: 'bg-slate-300 dark:bg-slate-600' },
        Processing: { badge: 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400', dot: 'bg-blue-500', accentBar: 'bg-blue-400' },
        Success: { badge: 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400', dot: 'bg-emerald-500', accentBar: 'bg-emerald-500' },
        Failed: { badge: 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-400', dot: 'bg-red-500', accentBar: 'bg-red-400' },
        Cancelled: { badge: 'bg-slate-100 dark:bg-slate-500/10 text-slate-500 dark:text-slate-400', dot: 'bg-slate-400', accentBar: 'bg-slate-300' },
        Refunded: { badge: 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400', dot: 'bg-purple-500', accentBar: 'bg-purple-400' },
    };
    return map[status] || map.Pending;
}

const parseUserAgent = (ua = "") => {
    const parser = new UAParser(ua);
    const result = parser.getResult();

    return {
        browser: `${result.browser.name || "Unknown"} ${result.browser.version || ""}`.trim(),
        os: `${result.os.name || "Unknown"} ${result.os.version || ""}`.trim(),
        device: result.device.type || "Desktop",
    };
};

// ==========================================
// 5. Computed (Calculations & Timeline)
// ==========================================

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

// ==========================================
// 6. Lifecycle Hooks
// ==========================================
onMounted(async () => {
    await Promise.all([
        fetchOrderDetails(),
        getCartItems()
    ]);

    // let printed = false;
    // let closed = false;

    // const safeClose = () => {
    //     if (closed) return;
    //     closed = true;

    //     // cleanup
    //     window.onafterprint = null;
    //     window.onfocus = null;
    //     document.removeEventListener("visibilitychange", onVisibility);

    //     // close (some browsers need small delay)
    //     setTimeout(() => window.close(), 50);
    // };

    // const onVisibility = () => {
    //     if (!document.hidden && printed) {
    //     safeClose();
    //     }
    // };

    // document.addEventListener("visibilitychange", onVisibility);

    // window.onafterprint = () => {
    //     safeClose();
    // };

    // window.onfocus = () => {
    //     if (printed) safeClose();
    // };

    // setTimeout(() => {
    //     if (printed) return;
    //     printed = true;
    //     window.print();
    // }, 300);

    // setTimeout(() => {
    //     if (printed) safeClose();
    // }, 15000);
});
</script>

<style scoped>
/* Full A4 portrait page */
.a4-page {
  width: 210mm;
  min-height: 297mm;
  background: #fff;
  margin: 0 auto;
  padding: 16mm 14mm;
  box-sizing: border-box;
  color: #0f172a;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif;
  position: relative;
}

/* Header */
.header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}
.header-left h1 { font-size: 24px; font-weight: 800; margin: 0 0 4px 0; letter-spacing: 0.3px; }
.header-left p { font-size: 11px; margin: 2px 0; color: #334155; }
.header-left .meta { font-size: 10.5px; color: #475569; }

.header-right { text-align: right; }
.header-right h2 { font-size: 26px; font-weight: 900; margin: 0; letter-spacing: 2px; color: #4338ca; }
.header-right .inv-no { font-size: 13px; font-weight: 700; margin: 4px 0 0 0; }
.header-right .inv-date { font-size: 11px; color: #475569; margin: 2px 0 0 0; }

.divider { margin: 10px 0 14px 0; border: none; border-top: 2px solid #0f172a; }

/* Two column info */
.two-col {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8mm;
  margin-bottom: 8mm;
}

.card {
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  overflow: hidden;
  break-inside: avoid;
}
.card-header {
  background: #4338ca;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  padding: 6px 10px;
  letter-spacing: 0.3px;
}
.card-body { padding: 8px 10px; }
.mt-gap { margin-top: 6mm; }

.row {
  display: flex;
  justify-content: space-between;
  gap: 8px;
  padding: 3px 0;
  border-bottom: 1px dashed #e2e8f0;
  font-size: 11.5px;
}
.row:last-child { border-bottom: none; }
.label { color: #64748b; }
.value { font-weight: 600; text-align: right; word-break: break-word; }
.highlight-green { color: #059669; }

/* Items table */
.items-section {
  margin-bottom: 8mm;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  overflow: hidden;
}

.section-title {
  background: #4338ca;
  color: #fff;
  font-size: 12px;
  font-weight: 700;
  padding: 7px 12px;
  letter-spacing: 0.4px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 11.5px;
}

.items-table thead th {
  background: #eef2ff;
  color: #3730a3;
  text-align: left;
  padding: 8px 12px;
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  border-bottom: 1.5px solid #cbd5e1;
}

.items-table tbody td {
  padding: 8px 12px;
  border-bottom: 1px solid #f1f5f9;
  vertical-align: top;
}

.items-table tbody tr:nth-child(even) {
  background: #f8fafc;
}

.items-table tbody tr:last-child td {
  border-bottom: 1.5px solid #cbd5e1;
}

.item-name {
  font-weight: 600;
  color: #0f172a;
}

.item-variant,
.item-note {
  color: #64748b;
  font-size: 10px;
  margin-top: 1px;
}

.items-table tfoot td {
  padding: 8px 12px;
  font-size: 12px;
  background: #f8fafc;
}

.foot-label {
  color: #64748b;
  font-weight: 600;
}

.foot-value {
  font-weight: 800;
  color: #4338ca;
  font-size: 13px;
}

.col-sl { width: 5%; }
.col-item { width: 38%; }
.col-qty { width: 9%; }
.col-price { width: 16%; }
.col-discount { width: 14%; }
.col-total { width: 18%; }

.text-center { text-align: center; }
.text-right { text-align: right; }
.text-red { color: #dc2626; }

/* Bottom grid */
.bottom-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8mm;
  margin-bottom: 8mm;
  align-items: start;
}

.summary-table { width: 100%; border-collapse: collapse; font-size: 12px; }
.summary-table td { padding: 5px 4px; }
.summary-table tr.total-row td {
  font-weight: 800;
  border-top: 2px solid #0f172a;
  padding-top: 8px;
  font-size: 13.5px;
  color: #4338ca;
}
.summary-table tr.due-row td {
  font-weight: 800;
  border-top: 1px dashed #cbd5e1;
  padding-top: 6px;
}

/* Signatures */
.signatures {
  display: flex;
  justify-content: space-between;
  gap: 12mm;
  margin-top: 14mm;
}
.sig { width: 33%; text-align: center; font-size: 11px; color: #0f172a; }
.sig-line { width: 80%; margin: 0 auto 4px auto; border-top: 1.5px solid #0f172a; }

/* Footer */
.footer {
  text-align: center;
  margin-top: 10mm;
  padding-top: 4mm;
  border-top: 1px solid #e2e8f0;
  font-size: 10px;
  color: #64748b;
}

.debug { text-align: center; font-size: 11px; color: #64748b; margin-top: 6px; }
.debug.error { color: #dc2626; }

/* Print rules */
@media print {
  @page { size: A4 portrait; margin: 0; }

  :global(html), :global(body) {
    margin: 0 !important;
    padding: 0 !important;
    background: #fff !important;
  }

  .a4-page { width: 210mm; min-height: 297mm; padding: 14mm 14mm; }
  .debug { display: none !important; }
}
</style>