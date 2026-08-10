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
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
                            <div class="flex items-center gap-4">
                            <button @click="$router.back()" class="p-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800 transition shadow-sm">
                                <i class="fa-solid fa-arrow-left-long text-slate-600 dark:text-slate-400"></i>
                            </button>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 dark:border-slate-800/60 pb-5">
                                    <!-- Left Side: Order Info -->
                                    <div class="space-y-1.5">
                                        <div class="flex flex-wrap items-center gap-2.5">
                                        <!-- Order Number -->
                                        <h1 class="text-xl font-bold tracking-tight text-slate-900 dark:text-white flex items-center gap-1.5">
                                            Order <span class="text-indigo-600 dark:text-indigo-400">#{{ order.reg }}</span>
                                        </h1>
                                        
                                        <span 
                                            v-if="order.coupon_id" 
                                            class="inline-flex items-center gap-1 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 font-mono text-xs font-semibold px-2 py-0.5 rounded-full border border-amber-200/60 dark:border-amber-900/50 uppercase tracking-wider"
                                            title="Coupon Applied">                                        
                                            <i class="fa-solid fa-tags text-amber-500"></i>
                                            {{ order.coupon_code }}
                                        </span>
                                        </div>

                                        <!-- Date Section -->
                                        <p class="text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar text-slate-400 dark:text-slate-500"></i>
                                        Placed on {{ formatDate(order.date) }}
                                        </p>
                                    </div>
                                    <!-- Right Side: Order Info -->
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
                                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Payment Date</p>
                                        <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">
                                            {{ order.paid_at ? formatDate(order.paid_at) : 'Waiting for Payment' }}
                                        </p>
                                    </div>
                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Total Amount</p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ order.currency }} ৳ {{ order.amount.toLocaleString() }}</p>
                                    </div>
                                    <div 
                                        @click="openStatusModal"
                                        :class="(order.status === 'cancelled' || order.status === 'delivered')
                                            ? 'opacity-50 cursor-not-allowed'
                                            : ''"
                                        class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm cursor-pointer hover:border-indigo-500 dark:hover:border-indigo-500 transition-all group">
                                        <div class="flex justify-between items-start">
                                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Order Status</p>
                                            <i class="fa-solid fa-pencil h-4 w-4 text-slate-400 opacity-0 group-hover:opacity-100 transition"></i>
                                        </div>
                                        
                                        <span :class="getStatus(order.status).container" class="px-3 py-1 rounded-lg text-[11px] font-bold uppercase inline-flex items-center gap-2 border border-transparent dark:border-current/10">
                                            <span class="h-2 w-2 rounded-full" :class="getStatus(order.status).dot"></span>
                                            {{ order.status }}
                                        </span>
                                    </div>
                                    <div class="bg-white dark:bg-slate-900 p-5 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
                                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">Total Point</p>
                                        <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ order.point.toLocaleString() }}</p>
                                    </div>
                                </div>

                                <div class="bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
                                    <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800">
                                        <h3 class="font-bold text-slate-900 dark:text-white">Transaction Information</h3>
                                    </div>
                                    <div class="p-6">
                                        <div class="space-y-4">
                                            <div class="flex justify-between items-center py-2">
                                            <span class="text-sm text-slate-500 italic">Transaction ID:</span>
                                            <span class="text-sm font-mono font-bold text-slate-900 dark:text-indigo-400 bg-slate-50 dark:bg-indigo-500/5 px-3 py-1 rounded-lg border border-slate-100 dark:border-indigo-500/10">
                                                {{ order.transaction_id || 'Not Available' }}
                                            </span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-t border-slate-50 dark:border-slate-800/50">
                                            <span class="text-sm text-slate-500 italic">Registration Number:</span>
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ order.reg }}</span>
                                            </div>
                                            <div class="flex justify-between items-center py-2 border-t border-slate-50 dark:border-slate-800/50">
                                            <span class="text-sm text-slate-500 italic">Currency Used:</span>
                                            <span class="text-sm font-bold text-slate-900 dark:text-white">{{ order.currency }} (Bangladeshi Taka)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center justify-between mb-4">
                                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                            Order Items ({{ cartItems.length }})
                                        </h3>
                                    </div>

                                    <div v-for="item in cartItems" :key="item.id"
                                        class="group bg-white dark:bg-slate-900 p-4 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all duration-300">
                                    
                                        <div class="flex flex-col sm:flex-row items-center gap-6">
                                            
                                            <div class="relative h-24 w-24 flex-shrink-0 bg-slate-100 dark:bg-slate-800 rounded-xl overflow-hidden border border-slate-200 dark:border-slate-700">
                                                
                                                <img v-if="item.product?.image" :src="getProductImage(item)" :alt="item.product?.name || 'Product Image'" class="h-full w-full object-cover group-hover:scale-110 transition-transform duration-500">

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
                                                        <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter leading-none">
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
                                                    <div class="text-xl font-black text-slate-900 dark:text-white tracking-tight">
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
                                                    {{ order.user?.name.substring(0, 2).toUpperCase() }}
                                                </div>
                                                <div class="absolute -bottom-2 -right-2 bg-green-500 border-4 border-white dark:border-slate-900 h-6 w-6 rounded-full" title="Active User"></div>
                                            </div>

                                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ order.user?.name }}</h4>
                                            <!-- <p class="text-sm text-slate-500 mb-6">Customer ID: {{ order.user_id }}</p> -->
                                            <p class="text-sm text-slate-500 mb-6">UID#{{ order.user?.user_id }}</p>
                                        
                                            <div class="text-left space-y-3 border-t border-slate-100 dark:border-slate-800 py-4">
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-envelope"></i>
                                                    <span>{{ order.user?.email }}</span> </div>
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-location-dot"></i>
                                                    <span>{{ order.user?.present_address }} || {{ order.user?.permanent_address }}</span>
                                                </div>
                                            </div>

                                            <div class="text-left space-y-3 border-t border-slate-100 dark:border-slate-800 pt-6">
                                                <!-- Contact Name -->
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-user w-4 text-center"></i>
                                                    <span class="font-semibold">{{ order.contact_name }}</span>
                                                </div>

                                                <!-- Contact Number -->
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-phone w-4 text-center"></i>
                                                    <span>{{ order.contact_number }}</span>
                                                </div>

                                                <!-- Contact Email -->
                                                <div class="flex items-center gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-regular fa-envelope w-4 text-center"></i>
                                                    <span>{{ order.contact_email }}</span>
                                                </div>

                                                <!-- Shipping Address -->
                                                <div class="flex items-start gap-3 text-sm text-slate-600 dark:text-slate-400">
                                                    <i class="fa-solid fa-location-dot w-4 text-center mt-0.5"></i>
                                                    <span class="leading-relaxed">{{ order.shipping_address }}</span>
                                                </div>

                                                <!-- Transaction ID -->
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

                                                <!-- Remarks -->
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
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
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
import api, {makeImg} from '../../src/services/api.js';

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
async function fetchOrderDetails(){
    loading.value = true;
    try{
        const reg = route.params.reg;
        if (!reg) {
            errorMsg.value = "Invalid order reference.";
            return;
        }

        const res = await api.get(`/orders/${route.params.reg}`);
        order.value = res.data.data;
        console.log(order.value);
    } catch (err) {
        errorMsg.value =
            err.response?.data?.message ||
            err.message ||
            "Something went wrong while fetching order.";

        console.log(err);
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

        console.log(err);
    } finally {
        loading.value = false;

        setTimeout(() => {
            isStatusModalOpen.value = false;
        }, 200);
    }

    console.log(`Status updated to: ${newStatus}`);
}












// Get order cart items
const cartItems = ref([]);
async function getCartItems() {
    loading.value = true;
    try {
        const res = await api.get(`/cart/${route.params.reg}`);
        cartItems.value = res.data.data;
        // console.log(cartItems.value);
    } catch (err) {
        console.error(err);
        errorMsg.value = err || "Something is wrong";
    } finally {
        loading.value = false;
    }
}

const defaultProductImage = "/images/product/default-product.webp";

const getProductImage = (item) => {
    // Nested optional chaining 
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