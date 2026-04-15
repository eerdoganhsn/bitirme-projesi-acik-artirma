<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    is_seller: false, // 2. BUNLARIN EKLİ OLDUĞUNDAN EMİN OL
    store_name: '',   // BUNLARIN EKLİ OLDUĞUNDAN EMİN OL
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Kayıt Ol" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Ad Soyad" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="E-posta Adresi" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Şifre" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Şifre (Tekrar)"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6 border-t pt-4">
                <label class="flex items-center cursor-pointer">
                    <Checkbox name="is_seller" v-model:checked="form.is_seller" />
                    <span class="ml-2 text-sm text-gray-600 font-bold text-indigo-600">Bir mağaza açıp satış yapmak istiyorum</span>
                </label>
            </div>

            <div v-if="form.is_seller" class="mt-4 p-4 bg-indigo-50 rounded-md border border-indigo-100 transition-all">
                <InputLabel for="store_name" value="Mağaza Adınız" />

                <TextInput
                    id="store_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.store_name"
                    required
                    placeholder="Örn: Harika Antikalar"
                />

                <InputError class="mt-2" :message="form.errors.store_name" />
                <p class="text-xs text-gray-500 mt-2">Mağazanız onaylandıktan sonra ürün yüklemeye başlayabilirsiniz.</p>
            </div>

            <div class="mt-6 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Zaten kayıtlı mısınız?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Kayıt Ol
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
