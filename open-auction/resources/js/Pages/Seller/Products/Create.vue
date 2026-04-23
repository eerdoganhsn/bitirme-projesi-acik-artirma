<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, Link } from "@inertiajs/vue3";
import { ref } from "vue";
import axios from "axios";

defineProps({
    categories: Array,
});

const imagePreviews = ref([]);
const uploadProgress = ref({});

const form = useForm({
    title: "",
    category_id: "",
    listing_type: "direct",
    price: "",
    stock: 1,
    end_date: "",
    images: [],
    cover_index: 0,
});

const handleImageUpload = (e) => {
    const files = Array.from(e.target.files);

    files.forEach((file) => {
        // Önizleme oluştur
        const previewUrl = URL.createObjectURL(file);
        imagePreviews.value.push({
            url: previewUrl,
            name: file.name,
            status: "loading",
        });

        // Form verisini hazırla
        const formData = new FormData();
        formData.append("image", file);

        // Axios ile hemen yüklemeye başla
        axios
            .post(route("seller.products.temp-upload"), formData, {
                onUploadProgress: (progressEvent) => {
                    const percentCompleted = Math.round(
                        (progressEvent.loaded * 100) / progressEvent.total,
                    );
                    uploadProgress.value[file.name] = percentCompleted;
                },
            })
            .then((response) => {
                // Yükleme bittiğinde sunucudan gelen dosya yolunu form dizisine ekle
                form.images.push(response.data.path);
                // Durumu güncelle
                const img = imagePreviews.value.find(
                    (i) => i.name === file.name,
                );
                if (img) img.status = "success";
            })
            .catch((error) => {
                const img = imagePreviews.value.find(
                    (i) => i.name === file.name,
                );
                if (img) img.status = "error";
            });
    });
};

// Resmi silme fonksiyonu
const removeImage = (index) => {
    form.images.splice(index, 1);
    imagePreviews.value.splice(index, 1);

    // Eğer silinen resim kapaksa, kapağı ilk resme geri al
    if (form.cover_index === index) {
        form.cover_index = 0;
    } else if (form.cover_index > index) {
        form.cover_index--; // Silinen resimden sonrakilerin indexi kaydığı için
    }
};

const submit = () => {
    form.post(route("seller.products.store"), {
        forceFormData: true,
        onError: (errors) => {
            // Bu satır sana hatayı açıkça söyleyecek
            // Örn: "The images.0 must be an image" veya "The file is too large"
            alert("Hata Mesajı: " + JSON.stringify(errors));
            console.log(errors);
        },
    });
};
</script>

<template>
    <Head title="Yeni Ürün Ekle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Yeni Ürün Ekle
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div
                    class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100"
                >
                    <form
                        @submit.prevent="submit"
                        class="p-6 sm:p-10 space-y-10"
                    >
                        <div>
                            <h3
                                class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-5"
                            >
                                Temel Bilgiler
                            </h3>

                            <div class="space-y-6">
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-1.5"
                                        >Ürün Başlığı</label
                                    >
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        placeholder="Örn: iPhone 13 Pro Max 256GB"
                                        class="block w-full bg-white border border-gray-300 text-gray-900 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required
                                    />
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-1.5"
                                        >Kategori</label
                                    >
                                    <select
                                        v-model="form.category_id"
                                        class="block w-full bg-white border border-gray-300 text-gray-900 rounded-md px-4 py-2.5 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        required
                                    >
                                        <option value="" disabled>
                                            Kategori Seçin...
                                        </option>
                                        <option
                                            v-for="category in categories"
                                            :key="category.id"
                                            :value="category.id"
                                        >
                                            {{ category.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="space-y-4">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <label
                                            class="block text-sm font-bold text-gray-700"
                                            >Ürün Görselleri (Max 8)</label
                                        >
                                        <span class="text-xs text-gray-500"
                                            >{{ imagePreviews.length }} /
                                            8</span
                                        >
                                    </div>

                                    <div
                                        class="grid grid-cols-2 md:grid-cols-4 gap-4"
                                    >
                                        <div
                                            v-for="(
                                                img, index
                                            ) in imagePreviews"
                                            :key="img.id"
                                            class="relative aspect-square rounded-xl border border-gray-200 overflow-hidden group bg-gray-50"
                                        >
                                            <img
                                                :src="img.url"
                                                class="w-full h-full object-cover"
                                                :class="{
                                                    'opacity-50': img.loading,
                                                }"
                                            />

                                            <div
                                                v-if="img.loading"
                                                class="absolute inset-0 flex flex-col items-center justify-center p-4 bg-white/60"
                                            >
                                                <div
                                                    class="w-full bg-gray-200 rounded-full h-1.5 mb-2"
                                                >
                                                    <div
                                                        class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300"
                                                        :style="{
                                                            width:
                                                                img.progress +
                                                                '%',
                                                        }"
                                                    ></div>
                                                </div>
                                                <span
                                                    class="text-[10px] font-bold text-indigo-700"
                                                    >%{{ img.progress }}</span
                                                >
                                            </div>

                                            <div
                                                v-if="img.success"
                                                class="absolute top-2 right-2 bg-green-500 text-white rounded-full p-1 shadow-lg"
                                            >
                                                <svg
                                                    class="w-3 h-3"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="3"
                                                        d="M5 13l4 4L19 7"
                                                    ></path>
                                                </svg>
                                            </div>

                                            <div
                                                v-if="!img.loading"
                                                class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2"
                                            >
                                                <button
                                                    @click.prevent="
                                                        form.cover_index = index
                                                    "
                                                    class="px-2 py-1 text-[10px] bg-white text-gray-800 rounded font-bold hover:bg-indigo-600 hover:text-white transition-colors"
                                                >
                                                    {{
                                                        form.cover_index ===
                                                        index
                                                            ? "★ KAPAK"
                                                            : "Kapak Yap"
                                                    }}
                                                </button>
                                                <button
                                                    @click.prevent="
                                                        removeImage(index)
                                                    "
                                                    class="px-2 py-1 text-[10px] bg-red-600 text-white rounded font-bold"
                                                >
                                                    Sil
                                                </button>
                                            </div>

                                            <div
                                                v-if="
                                                    form.cover_index === index
                                                "
                                                class="absolute top-2 left-2 bg-indigo-600 text-white text-[9px] font-black px-2 py-0.5 rounded shadow"
                                            >
                                                KAPAK
                                            </div>
                                        </div>

                                        <label
                                            v-if="imagePreviews.length < 8"
                                            class="aspect-square flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl hover:bg-indigo-50 hover:border-indigo-400 cursor-pointer transition-all group"
                                        >
                                            <svg
                                                class="w-8 h-8 text-gray-400 group-hover:text-indigo-500 mb-2"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M12 4v16m8-8H4"
                                                ></path>
                                            </svg>
                                            <span
                                                class="text-xs font-medium text-gray-500 group-hover:text-indigo-600"
                                                >Resim Ekle</span
                                            >
                                            <input
                                                type="file"
                                                @change="handleImageUpload"
                                                multiple
                                                accept="image/*"
                                                class="hidden"
                                            />
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-5"
                            >
                                Satış Şekli
                            </h3>

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <label
                                    :class="{
                                        'border-indigo-500 ring-1 ring-indigo-500 bg-indigo-50/30':
                                            form.listing_type === 'direct',
                                        'border-gray-200 bg-white hover:bg-gray-50':
                                            form.listing_type !== 'direct',
                                    }"
                                    class="relative flex cursor-pointer rounded-lg border p-5 shadow-sm focus:outline-none transition-all duration-200"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.listing_type"
                                        value="direct"
                                        class="sr-only"
                                    />
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span
                                                :class="{
                                                    'text-indigo-900':
                                                        form.listing_type ===
                                                        'direct',
                                                    'text-gray-900':
                                                        form.listing_type !==
                                                        'direct',
                                                }"
                                                class="block text-sm font-bold"
                                                >Hemen Al</span
                                            >
                                            <span
                                                class="mt-1.5 flex items-center text-xs leading-relaxed text-gray-500"
                                                >Standart e-ticaret satışı.
                                                Sabit fiyat ve stok
                                                belirlersiniz.</span
                                            >
                                        </span>
                                    </span>
                                    <span
                                        v-if="form.listing_type === 'direct'"
                                        class="text-indigo-600 ml-3"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </label>

                                <label
                                    :class="{
                                        'border-purple-500 ring-1 ring-purple-500 bg-purple-50/30':
                                            form.listing_type === 'auction',
                                        'border-gray-200 bg-white hover:bg-gray-50':
                                            form.listing_type !== 'auction',
                                    }"
                                    class="relative flex cursor-pointer rounded-lg border p-5 shadow-sm focus:outline-none transition-all duration-200"
                                >
                                    <input
                                        type="radio"
                                        v-model="form.listing_type"
                                        value="auction"
                                        class="sr-only"
                                    />
                                    <span class="flex flex-1">
                                        <span class="flex flex-col">
                                            <span
                                                :class="{
                                                    'text-purple-900':
                                                        form.listing_type ===
                                                        'auction',
                                                    'text-gray-900':
                                                        form.listing_type !==
                                                        'auction',
                                                }"
                                                class="block text-sm font-bold"
                                                >Açık Artırma</span
                                            >
                                            <span
                                                class="mt-1.5 flex items-center text-xs leading-relaxed text-gray-500"
                                                >Ürünü ihaleye çıkarın. En
                                                yüksek teklifi veren
                                                kazansın.</span
                                            >
                                        </span>
                                    </span>
                                    <span
                                        v-if="form.listing_type === 'auction'"
                                        class="text-purple-600 ml-3"
                                    >
                                        <svg
                                            class="h-6 w-6"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <h3
                                class="text-xs font-bold tracking-wider text-gray-400 uppercase mb-5"
                            >
                                Fiyatlandırma ve Detaylar
                            </h3>

                            <div
                                v-if="form.listing_type === 'direct'"
                                class="grid grid-cols-1 sm:grid-cols-2 gap-6"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-1.5"
                                        >Satış Fiyatı (₺)</label
                                    >
                                    <div class="relative rounded-md shadow-sm">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                        >
                                            <span
                                                class="text-gray-500 font-medium"
                                                >₺</span
                                            >
                                        </div>
                                        <input
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            class="block w-full bg-white border border-gray-300 rounded-md py-2.5 pl-12 pr-4 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm font-medium text-gray-900"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-1.5"
                                        >Stok Adedi</label
                                    >
                                    <input
                                        v-model="form.stock"
                                        type="number"
                                        min="1"
                                        class="block w-full bg-white border border-gray-300 rounded-md py-2.5 px-4 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm font-medium text-gray-900"
                                    />
                                </div>
                            </div>

                            <div
                                v-if="form.listing_type === 'auction'"
                                class="grid grid-cols-1 sm:grid-cols-2 gap-6"
                            >
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-1.5"
                                        >Başlangıç Fiyatı (₺)</label
                                    >
                                    <div class="relative rounded-md shadow-sm">
                                        <div
                                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4"
                                        >
                                            <span
                                                class="text-purple-500 font-medium"
                                                >₺</span
                                            >
                                        </div>
                                        <input
                                            v-model="form.price"
                                            type="number"
                                            step="0.01"
                                            class="block w-full bg-white border border-gray-300 rounded-md py-2.5 pl-12 pr-4 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 sm:text-sm font-medium text-gray-900"
                                        />
                                    </div>
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-1.5"
                                        >Bitiş Tarihi ve Saati</label
                                    >
                                    <input
                                        v-model="form.end_date"
                                        type="datetime-local"
                                        class="block w-full bg-white border border-gray-300 rounded-md py-2.5 px-4 focus:border-purple-500 focus:ring-1 focus:ring-purple-500 sm:text-sm font-medium text-gray-900"
                                    />
                                </div>
                            </div>
                        </div>
                    </form>
                    <div
                        v-if="form.progress"
                        class="w-full bg-gray-200 rounded-full h-2.5 mb-4"
                    >
                        <div
                            class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300"
                            :style="{ width: form.progress.percentage + '%' }"
                        ></div>
                        <p
                            class="text-xs text-indigo-600 mt-1 font-bold text-right"
                        >
                            Resimler Yükleniyor: %{{ form.progress.percentage }}
                        </p>
                    </div>
                    <div
                        class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex items-center justify-end gap-4 sm:px-10"
                    >
                        <Link
                            :href="route('seller.products.index')"
                            class="text-sm font-semibold text-gray-600 hover:text-gray-900"
                        >
                            İptal
                        </Link>
                        <button
                            @click="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50 transition-all"
                        >
                            <svg
                                v-if="form.processing"
                                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                ></circle>
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                ></path>
                            </svg>

                            {{
                                form.processing
                                    ? "Ürün ve Resimler Yükleniyor..."
                                    : "Ürünü Kaydet"
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
