<template>
    <div class="min-h-screen bg-white dark:bg-slate-950 transition-colors duration-200">
        <HeaderSection
            @open-sidebar="sidebarOpen = true"
            @search="onSearch"
            :isDark="isDark" @toggle-theme="toggleTheme"
        />

        <div class="flex min-h-[calc(100vh-56px)]">
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
            <div class="min-h-screen w-full bg-gray-50 dark:bg-slate-950 transition-colors duration-200 p-6">
                <div class="mx-auto bg-white dark:bg-slate-900 shadow-lg rounded-2xl p-8 max-w-6xl">

                    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white"><i class="fa-solid fa-pen-to-square"></i> Edit Product</h2>

                    <form @submit.prevent="submitEdit" class="space-y-6">

                        <!-- Name -->
                        <div>
                            <label class="label">Product Name</label>
                            <input v-model="form.name" class="input" placeholder="e.g Classic Leather Backpack"/>
                            <p class="error" v-if="errors.name">{{ errors.name[0] }}</p>
                        </div>

                        <!-- SKU -->
                        <div>
                            <label class="label">SKU</label>
                            <input v-model="form.sku" class="input" placeholder="e.g L-BACKPACK-001"/>
                            <p class="error" v-if="errors.sku">{{ errors.sku[0] }}</p>
                        </div>

                        <!-- Brands -->
                        <div>
                            <label class="label">Brand</label>
                            <select v-model="form.brand">
                                <option disabled selected value="">-- Select Brand --</option>
                                <option v-for="brand in brands" :value="brand.id">{{ brand.name }}</option>
                            </select>
                            <p class="error" v-if="errors.brand">{{ errors.brand[0] }}</p>
                        </div>

                        <!-- Category + Subcategory -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Category</label>
                                <select v-model="form.category">
                                    <option disabled selected value="">-- Select Category --</option>
                                    <option v-for="category in categories" :value="category.id">{{ category.name }}</option>
                                </select>
                                <p class="error" v-if="errors.category">{{ errors.category[0] }}</p>
                            </div>

                            <div>
                                <label class="label">Sub Category</label>
                                <select v-model="form.subcategory">
                                    <option disabled selected value="">-- Select Sub-Category --</option>
                                    <option v-for="subcategory in filteredSubCategories" :value="subcategory.id">{{ subcategory.name }}</option>
                                </select>
                                <p class="error" v-if="errors.subcategory">{{ errors.subcategory[0] }}</p>
                            </div>
                        </div>

                        <!-- Price + Stock -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Price</label>
                                <input type="number" v-model="form.price" min="1" class="input" placeholder="e.g BDT ৳ 450.00"/>
                            </div>

                            <div>
                                <label class="label">Stock Quantity</label>
                                <input type="number" v-model="form.stock_quantity" class="input" placeholder="e.g 15 pcs"/>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Discount Price -->
                            <div>
                                <label class="label">Discount (Optional)</label>
                                <input type="number" v-model="form.discount" min="0" :max="form.price || 0"  @input="validateVariantDiscount(form)" class="input" placeholder="e.g BDT ৳ 400.00"/>
                                <p class="error" v-if="errors.discount">{{ errors.discount[0] }}</p>
                            </div>
                            <div>
                                <label class="label">Point</label>
                                <input type="number" v-model="form.point" class="input" placeholder="e.g 100"/>
                                <p class="error" v-if="errors.point">{{ errors.point[0] }}</p>
                            </div>
                        </div>

                        <!-- Minimum Stock -->
                        <div>
                            <label class="label">Minimum Stock Alert</label>
                            <input type="number" v-model="form.min_stock" class="input" placeholder="e.g 5 pcs"/>
                            <p class="error" v-if="errors.min_stock">{{ errors.min_stock[0] }}</p>
                        </div>

                        <!-- Summary -->
                        <div>
                            <label class="label">Summary</label>
                            <input type="text" v-model="form.summary" class="input" placeholder="e.g Product Summary"/>
                            <p class="error" v-if="errors.summary">{{ errors.summary[0] }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="label">Description</label>
                            <textarea v-model="form.description" class="input" placeholder="e.g Detailed product description"></textarea>
                            <p class="error" v-if="errors.description">{{ errors.description[0] }}</p>
                        </div>

                        <!-- SLUG -->
                        <div>
                            <label class="label">SLUG</label>
                            <input type="text" v-model="form.slug" class="input" placeholder="e.g classic-leather-backpack-xz4a"/>
                            <p class="error" v-if="errors.slug">{{ errors.slug[0] }}</p>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <!-- Is Featured -->
                            <div>
                                <label class="label" for="is_featured">Is Featured</label>
                                <input type="checkbox" v-model="form.is_featured" id="is_featured" />
                                <label for="is_featured" class="ml-2 text-slate-900 dark:text-slate-50 cursor-pointer">Featured Product</label>
                            </div>

                            <!-- Is Sale -->
                            <div>
                                <label class="label" for="is_on_sale">Is On Sale</label>
                                <input type="checkbox" v-model="form.is_on_sale" id="is_on_sale"/>
                                <label class="ml-2 text-slate-900 dark:text-slate-50 cursor-pointer" for="is_on_sale">On Sale Product</label>
                            </div>

                            <!-- Is Active -->
                            <div>
                                <label class="label" for="is_active">Is Active</label>
                                <input type="checkbox" v-model="form.is_active" id="is_active"/>
                                <label class="ml-2 text-slate-900 dark:text-slate-50 cursor-pointer" for="is_active">Active Product</label>
                            </div>
                        </div>
                        
                        <!-- Meta title, keyword -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="label">Meta title</label>
                                <input type="text" v-model="form.meta_title" class="input" placeholder="e.g classic leather backpack"/>
                                <p class="error" v-if="errors.title">{{ errors.title[0] }}</p>
                            </div>
                            <div>
                                <label class="label">Meta keyword</label>
                                <input type="text" v-model="form.meta_keywords" class="input" placeholder="e.g classic, leather, backpack, brown, travel"/>
                                <p class="error" v-if="errors.keywords">{{ errors.keywords[0] }}</p>
                            </div>
                        </div>

                        <!-- Meta description -->
                        <div>
                            <label class="label">Meta description</label>
                            <textarea v-model="form.meta_description" class="input" placeholder="e.g Detailed product description"></textarea>
                            <p class="error" v-if="errors.description">{{ errors.description[0] }}</p>
                        </div>

                        <!-- Color Variant -->
                        <div class="space-y-4 border-y py-2 border-slate-200 dark:border-slate-700 pt-6">
                            <div class="flex items-center justify-between">
                                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Product Variants</h3>
                                <button 
                                    type="button" 
                                    @click="addVariant" 
                                    class="text-sm bg-blue-600 text-white px-3 py-1 rounded-lg hover:bg-blue-700 transition">
                                    <i class="fa-solid fa-plus"></i> Add Variant
                                </button>
                            </div>

                            <p v-if="form.variants.length === 0" class="text-sm text-slate-500 italic">No variants added. (Optional)</p>

                            <div v-for="(variant, index) in form.variants" :key="index" class="bg-slate-50 dark:bg-slate-800 p-4 rounded-2xl relative border border-slate-200 dark:border-slate-700">
                                <button 
                                    type="button" 
                                    @click="removeVariant(index)" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white w-6 h-6 rounded-full flex items-center justify-center hover:bg-red-600 shadow-sm">
                                    <i class="fa-solid fa-x"></i>
                                </button>

                                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 uppercase">Color</label>
                                        <input v-model="variant.color" type="text" class="input mt-1" placeholder="e.g. Red / #000"/>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 uppercase">Size</label>
                                        <input v-model="variant.size" type="text" class="input mt-1" placeholder="e.g. XL / 42"/>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 uppercase">Price</label>
                                        <input v-model="variant.price" type="number" min="1" class="input mt-1" placeholder="Variant Price"/>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 uppercase">Discount</label>
                                        <input v-model="variant.discount" type="number" min="0" :max="variant.price || 0"  @input="validateVariantDiscount(variant)" class="input mt-1" placeholder="Variant Price"/>
                                        <p v-if="variant.discount > variant.price" class="text-red-500 text-xs mt-1">Discount cannot be greater than price.</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-bold text-slate-500 uppercase">Stock</label>
                                        <input v-model="variant.stock_quantity" type="number" class="input mt-1" placeholder="Quantity"/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Multi Image Upload -->
                        <div>
                            <label class="label">Product Images</label>

                            <!-- Drag & Drop Zone -->
                            <div
                                class="rounded-2xl border-2 border-dashed p-5 transition bg-white dark:bg-slate-800"
                                :class="isDragOver ? 'border-blue-500 bg-blue-50 dark:bg-slate-700' : 'border-slate-300 dark:border-slate-600'"
                                @dragover.prevent="onDragOver"
                                @dragleave="onDragLeave"
                                @drop.prevent="onDrop">
                                <div class="flex items-center justify-between gap-4 flex-wrap">
                                <div class="text-sm text-slate-600 dark:text-slate-300">
                                    <div class="font-semibold text-slate-800 dark:text-white">Drag & drop images here</div>
                                    <div class="text-xs text-slate-500 dark:text-slate-400">or click Browse (jpg, png, webp)</div>
                                </div>

                                <!-- Browse Button -->
                                <label class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white cursor-pointer hover:bg-slate-800">
                                    Browse
                                    <input
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    multiple
                                    @change="handleImage"
                                    />
                                </label>
                                </div>

                                <!-- Preview -->
                                <div v-if="preview.length" class="mt-4 grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    <div v-for="(img, idx) in preview" :key="idx" class="relative">
                                        <img :src="img.url"  class="h-full w-full rounded-xl border object-cover"/>
                                        <button type="button" class="absolute top-1 right-1 bg-red-600 text-white rounded-full p-1 hover:bg-red-700" @click="removeImage(idx)">
                                        ✕
                                        </button>
                                        <p class="text-xs text-slate-500 mt-1 text-center truncate dark:text-slate-300">
                                            {{ img.file?.name || img.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-8 border-t border-slate-200 dark:border-slate-800 mt-8">
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button 
                                    type="button" 
                                    @click="router.back()" 
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all font-medium text-sm shadow-sm">
                                    <i class="fa-solid fa-arrow-left text-xs"></i>
                                    Back
                                </button>

                                <button 
                                    type="button" 
                                    @click="handleDelete"
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-red-100 dark:border-red-900/30 bg-red-50 dark:bg-red-900/10 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white dark:hover:bg-red-600 transition-all font-medium text-sm">
                                    <i v-if="loading" class="fa-solid fa-circle-notch animate-spin"></i>
                                    <i v-else class="fa-solid fa-trash-can text-xs"></i>
                                    {{ loading ? 'Deleting...' : 'Delete' }}
                                </button>
                            </div>

                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <button 
                                    type="button" 
                                    @click="resetForm()" 
                                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-all font-medium text-sm">
                                    <i class="fa-solid fa-rotate-right text-xs"></i>
                                    Reset
                                </button>

                                <button 
                                    :disabled="loading" 
                                    type="submit"
                                    class="flex-[2] sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-2.5 rounded-xl bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-70 disabled:cursor-not-allowed shadow-lg shadow-blue-500/20 transition-all font-bold text-sm">
                                    
                                    <i v-if="loading" class="fa-solid fa-circle-notch animate-spin"></i>
                                    <i v-else class="fa-solid fa-check"></i>
                                    
                                    {{ loading ? 'Updating...' : 'Update Changes' }}
                                </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
        <FooterSection />
    </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api, { makeImg } from '../../../services/api.js'

import Navbar from "../admin/admin-navbar.vue";
import HeaderSection from "../admin/admin-header.vue";
import Message from '../../Message/message.vue'
import FooterSection from "../../e-commerce/footer.vue";

const router = useRouter()
const route = useRoute()

const loading = ref(false)
const errors = reactive({})
const successMsg = ref('')
const errorMsg = ref('')

const active = ref('dashboard')
const isDark = ref(false)
const sidebarOpen = ref(false)
const isDragOver = ref(false)
const preview = ref([])














// --- INITIAL FORM ---
const initialForm = {
    id: null,
    name: '',
    sku: '',
    category: '',
    subcategory: '',
    brand: '',
    price: 0,
    discount: 0,
    stock_quantity: 0,
    min_stock: 0,
    summary: '',
    description: '',
    slug: '',
    is_featured: false,
    is_on_sale: false,
    is_active: true,
    title: '',
    keywords: '',
    meta_title: '',
    meta_keywords: '',
    meta_description: '',
    variants: [],
    images: [],
    point: '',
}

const form = reactive({ ...initialForm })

// --- RESET FORM ---
function resetForm() {
    Object.keys(initialForm).forEach(key => {
        form[key] = Array.isArray(initialForm[key]) ? [] : initialForm[key]
    })
    preview.value = []
    Object.keys(errors).forEach(key => delete errors[key])
    successMsg.value = ''
    errorMsg.value = ''
}














// --- FETCH PRODUCT ---
async function fetchProduct() {
    loading.value = true
    try {
        const res = await api.get(`/products/${route.params.slug}`)
        const product = res.data.data

        Object.assign(form, {
            id: product.id,
            name: product.name,
            sku: product.sku,
            category: product.category_id,
            subcategory: product.subcategory_id,
            brand: product.brand_id,
            price: product.price,
            discount: product.discount,
            stock_quantity: product.stock_quantity,
            min_stock: product.min_stock,
            summary: product.summary,
            description: product.description,
            slug: product.slug,
            is_featured: !!product.is_featured,
            is_on_sale: !!product.is_on_sale,
            is_active: !!product.is_active,
            title: product.title,
            keywords: product.keywords,
            meta_title: product.meta_title,
            meta_keywords: product.meta_keywords,
            meta_description: product.meta_description,
            variants: product.variants || [],
            point: product.point,
        })

        // Preview images
        preview.value = (product.images || []).map(img => ({
            file: null,
            url: img.url || '',
            name: img.image_path ? img.image_path.split('/').pop() : 'Image'
        }))

    } catch(err) {
        console.error(err)
        errorMsg.value = 'Failed to load product details.'
    } finally {
        loading.value = false
    }
}













// --- FETCH CATEGORIES / SUBCATEGORIES / BRANDS ---
const categories = ref([])
const subcategories = ref([])
const brands = ref([])

async function fetchCategories() {
    try {
        const res = await api.get('/products/get-categories')
        categories.value = res.data.data
    } catch(err) { console.error(err) }
}

async function fetchSubCategories() {
    try {
        const res = await api.get('/products/get-subcategories')
        subcategories.value = res.data.data
    } catch(err) { console.error(err) }
}

async function fetchBrands() {
    try {
        const res = await api.get('/products/get-brands')
        brands.value = res.data.data
    } catch(err) { console.error(err) }
}

const filteredSubCategories = computed(() => {
    if(!form.category) return []
    return subcategories.value.filter(sub => sub.category_id === form.category)
})

function validateVariantDiscount(variant) {
    const price = Number(variant.price) || 0;
    const discount = Number(variant.discount) || 0;

    if (discount > price) {
        variant.discount = price;
    }

    if (discount < 0) {
        variant.discount = 0;
    }
}










// --- VARIANTS ---
function addVariant() {
    form.variants.push({ color: '', size: '', price: form.price || 0, discount: 0, stock: 0 })
}
function removeVariant(i) { form.variants.splice(i, 1) }

// --- IMAGE HANDLING (REFACTORED) ---
function setFile(file) {
    if (!file.type.startsWith('image/')) return;
    
    form.images.push(file);
    
    preview.value.push({ 
        file: file, 
        url: URL.createObjectURL(file),
        isNew: true
    });
}

function handleImage(e) { 
    Array.from(e.target.files).forEach(setFile);
    e.target.value = '';
}

function removeImage(idx) {
    const target = preview.value[idx];
    
    if (target.file) {
        const fileIndex = form.images.indexOf(target.file);
        if (fileIndex > -1) form.images.splice(fileIndex, 1);
    }
    
    preview.value.splice(idx, 1);
}

function onDrop(e) { isDragOver.value = false; Array.from(e.dataTransfer.files).forEach(setFile) }
function onDragOver(e){ e.preventDefault(); isDragOver.value = true }
function onDragLeave(){ isDragOver.value = false }













// --- SUBMIT FORM ---
async function submitEdit() {
    loading.value = true
    errorMsg.value = ''
    Object.keys(errors).forEach(k => delete errors[k])

    // if(!form.id){ errorMsg.value = 'Product ID missing'; loading.value=false; return }

    try {
        const fd = new FormData();
        
        // Basic fields
        const fields = {
            name: form.name,
            sku: form.sku,
            brand: form.brand,
            category: form.category,
            subcategory: form.subcategory,
            price: form.price || 0,
            discount: form.discount || 0,
            stock_quantity: form.stock_quantity || 0,
            min_stock: form.min_stock || 0,
            summary: form.summary,
            description: form.description,
            slug: form.slug,
            meta_title: form.meta_title,
            meta_keywords: form.meta_keywords,
            meta_description: form.meta_description,
            is_featured: form.is_featured ? 1 : 0,
            is_on_sale: form.is_on_sale ? 1 : 0,
            is_active: form.is_active ? 1 : 0,
            point: form.point || 0,
        };


        Object.entries(fields).forEach(([key, value]) => {
            if (value !== null && value !== undefined) fd.append(key, value);
        });
        

        // Variants
        if (form.variants.length > 0) {
            form.variants.forEach((v, i) => {
                fd.append(`variants[${i}][color]`, v.color || '');
                fd.append(`variants[${i}][size]`, v.size || '');
                fd.append(`variants[${i}][price]`, v.price || 0);
                fd.append(`variants[${i}][discount]`, v.discount || 0);
                fd.append(`variants[${i}][stock_quantity]`, v.stock_quantity || 0);
            });
        }

        // Images
        if (form.images.length > 0) {
            form.images.forEach(f => fd.append('images[]', f));
        }

        fd.append('_method', 'PUT');

        const res = await api.post(`/products/update/${form.id}`, fd);

        successMsg.value = res.data.message || 'Product updated successfully!';
        await fetchProduct();
        // setTimeout(() => router.back(), 1000);

    } catch (err) {
        if (err.response?.status === 422) {
            Object.assign(errors, err.response.data.errors);
        }
        errorMsg.value = err.response?.data?.message || 'Something went wrong.';
    } finally {
        loading.value = false;
    }
}













// --- DELETE ---
async function handleDelete() {
    if (!form.id) {
        errorMsg.value = "Product ID missing";
        return;
    }

    if(confirm("Are you sure you want to delete this product?")){
        loading.value = true;
        errorMsg.value = '';

        try{
            const res = await api.delete(`/products/delete/${form.id}`);
            successMsg.value = res.data?.message || "Product deleted successfully.";
            setTimeout(() => {
                router.push('/products');
            }, 1000);
        }catch(err) {
            if(err.response?.data?.errors) Object.assign(errors, err.response.data.errors)
            errorMsg.value = err.response?.data?.message || 'Delete failed.';
        } finally { 
            loading.value = false;
        }
    }
}

















// --- THEME ---
function applyTheme(dark){
    isDark.value = dark
    document.documentElement.classList.toggle("dark", dark)
    localStorage.setItem("theme", dark ? "dark":"light")
}
function toggleTheme(){ applyTheme(!isDark.value) }

// --- SEARCH ---
function onSearch(q){ console.log("search:", q) }



















// --- INIT ---
onMounted(() => {
    fetchProduct()
    fetchCategories()
    fetchSubCategories()
    fetchBrands()

    window.addEventListener("keydown", e => { if(e.key==='Escape') sidebarOpen.value=false })

    const saved = localStorage.getItem("theme")
    if(saved==='dark') applyTheme(true)
    else if(saved==='light') applyTheme(false)
    else applyTheme(window.matchMedia("(prefers-color-scheme: dark)").matches)
})
</script>

<style scoped>
@reference "../style.css";

.label{
  @apply text-sm font-semibold text-slate-700 block mb-1 dark:text-slate-300;
}
.input, select{
  @apply w-full border rounded-xl px-3 py-2 focus:ring-2 focus:ring-blue-500 outline-none dark:bg-slate-800 dark:text-white dark:border-slate-600;
}
.error{
  @apply text-xs text-red-500 mt-1;
}
</style>

