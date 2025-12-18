<script setup lang="js">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
//import { type BreadcrumbItem } from '@/types';


import { IMaskComponent } from 'vue-imask';

import { ref, onMounted } from 'vue';









const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    centrifugo_listener:
    {
        status: String,

    },


});


/*

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();
*/
const breadcrumbItems = [
    {
        title: 'Настройки профиля',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;
const unmaskedPhone = ref('');


onMounted(() => {
    unmaskedPhone.value = user.phone || ''; // Предполагаем, что user.phone может быть null/undefined
});



const onAcceptUnmasked = (unmaskedValue) => {
    unmaskedPhone.value = unmaskedValue
}

</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Настройки профиля" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <HeadingSmall title="Информация" description="Изменение имени и почтового адреса" />

                <Form v-bind="ProfileController.update.form()" class="space-y-6"
                    v-slot="{ errors, processing, recentlySuccessful }">
                    <div class="grid gap-2">
                        <Label for="name">Имя</Label>
                        <Input id="name" class="mt-1 block w-full" name="name" :default-value="user.name" required
                            autocomplete="name" placeholder="Ваше имя" />
                        <InputError class="mt-2" :message="errors.name" />
                    </div>


                    <div class="grid gap-2">
                        <Label for="name">Телефон</Label>


                        <IMaskComponent required
                            class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm
      focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]  aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive"
                            mask="+{7}(000)00-00-00" autocomplete="phone" placeholder='Ваш телефон' id="phone_"
                            name="phone_" v-model:unmasked="user.phone" @accept:unmasked="onAcceptUnmasked" />
                        <input type="hidden" name="phone" :value="unmaskedPhone" />

                        <InputError class="mt-2" :message="errors.phone" />
                    </div>


                    <div class="grid gap-2">
                        <Label for="name">Телеграм</Label>


                        <IMaskComponent 
                            class="file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm
      focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]  aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive"
                            :mask="Number"  placeholder='Ваш Телеграм' id="tg_chat_id"
                            name="tg_chat_id" v-model:unmasked="user.tg_chat_id" />

                        <!--Input id="tg_chat_id" class="mt-1 block w-full" name="tg_chat_id"
                            :default-value="user.tg_chat_id" placeholder="Ваш Телеграм" /-->
                        <InputError class="mt-2" :message="errors.tg_chat_id" />
                    </div>








                    <div class="grid gap-2">
                        <Label for="email">Email </Label>
                        <Input id="email" type="email" class="mt-1 block w-full" name="email"
                            :default-value="user.email" required autocomplete="username" placeholder="Email " />
                        <InputError class="mt-2" :message="errors.email" />
                    </div>

                    <div v-if="mustVerifyEmail && !user.email_verified_at">
                        <p class="-mt-4 text-sm text-muted-foreground">
                            Ваш почтовый адрес не верифицирован
                            <Link :href="send()" as="button"
                                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                            Нажмите сюда чтобы отправить письмо для верификации.
                            </Link>
                        </p>

                        <div v-if="status === 'verification-link-sent'" class="mt-2 text-sm font-medium text-green-600">
                            Письмо со ссылкой для верификации отправлено на ваш адрес.
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <Button :disabled="processing" data-test="update-profile-button"
                            variant="design">Сохранить</Button>

                        <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                            leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                            <p v-show="recentlySuccessful" class="text-sm text-neutral-600">Сохранено.</p>
                        </Transition>
                    </div>
                </Form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>
