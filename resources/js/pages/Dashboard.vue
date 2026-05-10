<script setup>
import { ref, onMounted } from 'vue'
// import { router } from '@inertiajs/vue3' // Если понадобится навигация
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
// Импорт виджетов (убедитесь, что они тоже переведены на Vue)
import WidgetContainer from '@/components/dashboard/WidgetContainer.vue'
import IntegrationWidget from '@/components/dashboard/IntegrationWidget.vue'
import LocksPropertiesWidget from '@/components/dashboard/LocksPropertiesWidget.vue'
import LockBindingWidget from '@/components/dashboard/LockBindingWidget.vue'
import LockStatusWidget from '@/components/dashboard/LockStatusWidget.vue'
import QuickLockActions from '@/components/dashboard/QuickLockActions.vue'
import TabbedLogsSection from '@/components/dashboard/TabbedLogsSection.vue'
import { Centrifuge } from 'centrifuge'
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { test, dashboard, openLock, pincodes_list, getCodesList, deleteKey, addCodeToLock, pincodes_page, lockevents_lock_page } from '@/routes';
import { buttonVariants } from '@/components/ui/button'
import { cn } from "@/lib/utils"
import AddCodeDialog from '@/components/dashboard/AddCodeDialog.vue'

// 🔥 Импорты API (замените на ваши реальные вызовы)
// import { base44 } from '@/api/base44Client'

const page = usePage();

// Состояния
const isRefreshing = ref(false)
const selectedProperty = ref(null)
const selectedLock = ref()
const selectedCode = ref()


// Видимость виджетов
const visibleWidgets = ref({
    integration: true,
    locksProperties: true,
    lockBinding: true,
    lockStatus: true,
    quickActions: true,
    eventLog: true,
})


const props = defineProps({
    rents: {
        type: Object
    },
    locks: {
        type: Object
    },
    centrifugo_listener:
    {
        type: String,
        required: true,
    },
    token: {
        type: String
    },
    locks_count: {
        type: Number
    },


});



const keyList = ref()
const logList = ref()



const isOpenLockDialogOpen = ref(false) //******************* */
const waitApiOpenLock = ref(false)
const waitApiDeleteKey = ref(false)
const waitApiSyncKeys = ref(false)
const waitApiAddKey = ref(false)

const lockMessage = ref('')
const lockTitle = ref('')
const openLockResult = ref({})

const isAddKeyDialogOpen = ref(false)

const addKeyfn = async () => {
    selectedCode.value = null
    if (!selectedLock.value) return
    isAddKeyDialogOpen.value = true

}


const refreshKeysList = (lock) => {
    axios.post(pincodes_list(lock.id).url).then((response) => {
        keyList.value = response.data.pincodes
    })
        .catch((error) => {
            console.log(error);

        })
        .finally(() => {

        });
}


const onOpenChange = (state) => {
    isAddKeyDialogOpen.value = state
}



const goToKeyPage = async (page) => {

    axios.post(pincodes_page(selectedLock.value.id).url, { 'page': page, }).then((response) => {
        keyList.value = response.data.pincodes
    })
        .catch((error) => {
            console.log(error);

        })
        .finally(() => {

        });
    return true
}

const goToLogPage = async (page) => {

    axios.post(lockevents_lock_page(selectedLock.value.id).url, { 'page': page, }).then((response) => {
        logList.value = response.data.log_events
    })
        .catch((error) => {
            console.log(error);

        })
        .finally(() => {

        });
    return true
}



// Переключение видимости виджета
const toggleWidget = (widgetKey) => {
    
      console.log(visibleWidgets.value[widgetKey])
     
    visibleWidgets.value[widgetKey] = !visibleWidgets.value[widgetKey]
}

// Обработка выбора в быстрых действиях
const handleSelectionChange = (property, lock) => {
    selectedProperty.value = property
    selectedLock.value = lock

    refreshKeysList(selectedLock.value)
    goToLogPage(1)
}

// Загрузка при монтировании
onMounted(() => {
    const centrifuge = new Centrifuge(props.centrifugo_listener, {
        token: props.token
    })

    const sub = centrifuge.newSubscription('api:open_lock-' + page.props.auth.user.id)
    const sub_codes = centrifuge.newSubscription('api:get_codes_list-' + page.props.auth.user.id)
    const sub_delkey = centrifuge.newSubscription('api:delete_code_from_lock-' + page.props.auth.user.id)

    const sub_addkey = centrifuge.newSubscription('api:add_code_to_lock-' + page.props.auth.user.id)
    const sub_changekey = centrifuge.newSubscription('api:change_code-' + page.props.auth.user.id)


    //получение сообщений по веб.сокет
    sub.on('publication', (ctx) => {
        // console.log(ctx)
        waitApiOpenLock.value = false
        openLockResult.value.success = true
        openLockResult.value.msg = "Замок открыт"
        setTimeout(() => {
            openLockResult.value = {}
        }, 15000)
        // isOpenLockDialogOpen.value = true
        lockTitle.value = "Замок открыт"
        lockMessage.value = ctx?.data?.msg
    })



    //получение сообщений по веб.сокет
    sub_codes.on('publication', (ctx) => {
        refreshKeysList(selectedLock.value)
        waitApiSyncKeys.value = false
        // isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключи синхронизированы"
        openLockResult.value.success = true
        openLockResult.value.msg = "Ключи синхронизированы"
        setTimeout(() => {
            openLockResult.value = {}
        }, 15000)
        lockMessage.value = ctx?.data?.msg
    })



    sub_delkey.on('publication', (ctx) => {
        console.log(ctx)
        refreshKeysList(selectedLock.value)
        waitApiDeleteKey.value = false
        //isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключ удален"
        lockMessage.value = ctx?.data?.msg

    })


    sub_addkey.on('publication', (ctx) => {

        refreshKeysList(selectedLock.value)
        waitApiAddKey.value = false
        //isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключ добавлен"
        lockMessage.value = ctx?.data?.msg

        openLockResult.value.success = true
        openLockResult.value.msg = ctx?.data?.msg
        setTimeout(() => {
            openLockResult.value = {}
        }, 15000)

    })


    sub_changekey.on('publication', (ctx) => {

        refreshKeysList(selectedLock.value)
        waitApiAddKey.value = false
        //isOpenLockDialogOpen.value = true
        lockTitle.value = "Ключ изменен"
        lockMessage.value = ctx?.data?.msg

         openLockResult.value.success = true
        openLockResult.value.msg =  ctx?.data?.msg
        setTimeout(() => {
            openLockResult.value = {}
        }, 15000)

    })


    centrifuge.on('error', function (ctx) {
        console.log('ERROR: ', ctx);
        waitApiOpenLock.value = false
        waitApiSyncKeys.value = false
        waitApiDeleteKey.value = false
        waitApiAddKey.value = false
        openLockResult.value = {}
        /*isOpenLockDialogOpen.value = true
        lockTitle.value = "Ошибка"
        lockMessage.value = 'Ошибка'*/
    })

    centrifuge.connect()
    sub.subscribe()
    sub_codes.subscribe()
    sub_addkey.subscribe()
    sub_changekey.subscribe()
    sub_delkey.subscribe()
})







const openLockfn = async (lock) => {
    openLockResult.value = {}
    waitApiOpenLock.value = true
    try {
        const response = await axios.post(openLock().url, {
            'lock_id': lock.lock_id
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
    } catch (error) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }
}




const editCodefn = async (code) => {
    selectedCode.value = code
    isAddKeyDialogOpen.value = true


}




const refreshPinsfn = async (lock) => {
    openLockResult.value = {}
    waitApiSyncKeys.value = true
    try {
        const response = await axios.post(getCodesList().url, {
            'lock_id': lock.lock_id,
            'page_number': 1,
        }, {
            headers: {
                'Content-Type': 'application/json',
            }
        })
    } catch (error) {
        console.error('Error:', error)
    } finally {
        // loading.value = false
    }
}


</script>

<template>

    <Head title="Дашборд" />
    <div class="min-h-screen bg-white flex flex-col">

        <AppLayout>
            <div class="space-y-5">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold text-slate-900">Обзор</h1>
                </div>
                <!-- Widgets Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                    <!-- Integration Widget -->
                    <WidgetContainer v-if="visibleWidgets.integration" title="Интеграция"
                        @remove="toggleWidget('integration')">
                        <IntegrationWidget />
                    </WidgetContainer>

                    <!-- Locks & Properties Widget -->
                    <WidgetContainer v-if="visibleWidgets.locksProperties" title="Замки и объекты"
                        @remove="toggleWidget('locksProperties')">
                        <LocksPropertiesWidget :locks-count="locks_count" :properties-count="rents.length" />
                    </WidgetContainer>

                    <!-- Lock Binding Widget -->
                    <WidgetContainer v-if="visibleWidgets.lockBinding" title="Маппинг"
                        @remove="toggleWidget('lockBinding')">
                        <LockBindingWidget />
                    </WidgetContainer>
                </div>

                <!-- Lock Status Widget - Full Width -->
                <!--WidgetContainer v-if="visibleWidgets.lockStatus" title="Статусы замков"
                    @remove="toggleWidget('lockStatus')">
                    <LockStatusWidget :locks="locks" @refresh="handleRefresh" :is-refreshing="isRefreshing" />
                </WidgetContainer-->

                <!-- Quick Actions Widget - Full Width -->
                <WidgetContainer v-if="visibleWidgets.quickActions" title="Быстрые действия"
                    @remove="toggleWidget('quickActions')"
                    class="border-2 border-indigo-200 ring-1 ring-indigo-100 shadow-md shadow-indigo-50">
                    <QuickLockActions :locks="locks" :properties="rents" @open-lock="openLockfn"
                        @refresh-pins="refreshPinsfn" @selection-change="handleSelectionChange"
                        :keysLoading="waitApiSyncKeys" :openLockLoading="waitApiOpenLock"
                        :openLockResult="openLockResult" @add-code="addKeyfn" />
                </WidgetContainer>

                <!-- Коды и журнал событий -->
                <TabbedLogsSection v-if="visibleWidgets.eventLog && selectedLock" @key-page="goToKeyPage"
                    @refresh="goToKeyPage(1)" :selected-property="selectedProperty" @edit-code="editCodefn"
                    @start-delete="waitApiDeleteKey = true" :isDeleting="waitApiDeleteKey" :selected-lock="selectedLock"
                    :keys="keyList" :logs="logList" @log-page="goToLogPage" />
            </div>
        </AppLayout>


        <!-- Диалог добавления кода -->

        <AddCodeDialog v-model:open="isAddKeyDialogOpen" 
            :selected-lock="selectedLock" :onOpenChange="onOpenChange"  :code="selectedCode" />

    </div>

</template>