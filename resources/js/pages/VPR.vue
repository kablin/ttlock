<template>
    <div class="min-h-screen bg-gray-50 py-8 px-4">
        <div class="max-w-3xl mx-auto">
            <!-- === БЛОК НАСТРОЙКИ === -->
            <div v-if="phase === 'setup'" class="bg-white p-6 rounded-xl shadow-md mb-6">
                <h1 class="text-2xl font-bold text-gray-800 mb-4">Настройка тестирования</h1>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Количество случайных
                            вопросов</label>
                        <input v-model.number="randomCount" type="number" min="0"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Обязательные номера (ID или индекс)
                            через запятую</label>
                        <input v-model="mandatoryInput" type="text" placeholder="Например: 450192, 2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition" />
                    </div>
                </div>
                <button @click="startTest"
                    class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition shadow-sm">
                    Начать тестирование
                </button>
            </div>

            <!-- === СПИСОК ВОПРОСОВ === -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-3">
                    {{ phase === 'setup' ? 'Все вопросы (предпросмотр)' : 'Тестирование' }}
                    <span v-if="phase === 'test'" class="text-sm font-normal text-gray-500 ml-2">
                        (Отвечено: {{ answeredCount }} / {{ selectedQuestions.length }})
                    </span>
                </h2>

                <div class="space-y-4">
                    <div v-for="(q, index) in activeQuestions" :key="q.id" class="bg-white p-5 rounded-xl shadow">
                        <h3 class="font-semibold text-lg text-gray-800 mb-3">{{ index + 1 }}. &nbsp; {{ q.name }}</h3>
                        <div class="space-y-2">
                            <label v-for="resp in q.responses" :key="resp.id"
                                class="flex items-center p-3 border rounded-lg cursor-pointer transition hover:bg-gray-50"
                                :class="getOptionClasses(q, resp)">
                                <input type="checkbox" :name="'question-' + q.id" :value="resp.id"
                                    v-model="answers[q.id]" :disabled="phase === 'results'"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" />
                                <span class="ml-3 text-gray-700">{{ resp.name }}</span>

                                <!-- Иконки результатов -->
                                <span v-if="phase === 'results'" class="ml-auto font-bold text-lg">
                                    <span v-if="resp.correct && answers[q.id]?.includes(resp.id)"
                                        class="text-green-600">✓</span>
                                    <span v-else-if="!resp.correct && answers[q.id]?.includes(resp.id)"
                                        class="text-red-600">✗</span>
                                    <span v-else-if="resp.correct && !answers[q.id]?.includes(resp.id)"
                                        class="text-orange-500">◌</span>
                                </span>
                            </label>
                        </div>
                        <p v-if="phase === 'results'" class="mt-2 text-sm font-medium"
                            :class="isQuestionCorrect(q) ? 'text-green-600' : 'text-red-600'">
                            {{ isQuestionCorrect(q) ? '✓ Верно' : '✗ Неверно' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- === КНОПКИ УПРАВЛЕНИЯ === -->
            <div class="flex flex-col md:flex-row gap-4 justify-center pb-8">
                <button v-if="phase === 'test'" @click="finishTest"
                    class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-8 rounded-lg transition shadow-sm">
                    Завершить тест
                </button>
                <button v-if="phase === 'results'" @click="resetTest"
                    class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-8 rounded-lg transition shadow-sm">
                    Пройти заново
                </button>
            </div>

            <!-- === БЛОК РЕЗУЛЬТАТОВ === -->
            <div v-if="phase === 'results'" class="bg-white p-6 rounded-xl shadow-md text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">Результаты</h2>
                <div class="text-5xl font-extrabold text-blue-600 mb-2">{{ results.correct }} / {{ results.total }}
                </div>
                <p class="text-gray-600 mb-4">Правильных ответов: {{ Math.round(results.correct / results.total * 100)
                    }}%</p>
                <div class="w-full bg-gray-200 rounded-full h-4 mb-2 overflow-hidden">
                    <div class="bg-green-500 h-4 rounded-full transition-all duration-500"
                        :style="{ width: `${(results.correct / results.total) * 100}%` }"></div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// ==========================================
// СОСТОЯНИЕ
// ==========================================
const phase = ref('setup')
const randomCount = ref(1)
const mandatoryInput = ref('')
const selectedQuestions = ref([])
const answers = ref({}) // { questionId: [answerId, answerId, ...] }
const results = ref({ correct: 0, total: 0 })

const activeQuestions = computed(() => {
    if (phase.value === 'setup') return QUESTIONS
    return selectedQuestions.value
})

const answeredCount = computed(() => {
    return Object.values(answers.value).filter(arr => arr?.length > 0).length
})

// ==========================================
// ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
// ==========================================

// Перемешивание массива (Fisher-Yates)
const shuffleArray = (array) => {
    const arr = [...array] // Копия, чтобы не мутировать оригинал
    for (let i = arr.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1))
            ;[arr[i], arr[j]] = [arr[j], arr[i]]
    }
    return arr
}

const isQuestionCorrect = (question) => {
    const selected = answers.value[question.id] || []
    const correctIds = question.responses.filter(r => r.correct).map(r => r.id)

    const allCorrectSelected = correctIds.every(id => selected.includes(id))
    const noIncorrectSelected = selected.every(id => correctIds.includes(id))

    return allCorrectSelected && noIncorrectSelected
}

const getOptionClasses = (question, response) => {
    if (phase.value !== 'results') {
        let d = answers.value[question.id]?.includes(response.id)
            ? 'bg-blue-50 border-blue-400'
            : 'border-gray-200'
        if (response.correct && phase.value == 'setup') d = d + ' font-bold'
        return d
    }

    const isSelected = answers.value[question.id]?.includes(response.id)

    if (response.correct && isSelected) return 'border-green-400 bg-green-50'
    if (!response.correct && isSelected) return 'border-red-400 bg-red-50'
    if (response.correct && !isSelected) return 'border-orange-300 bg-orange-50'
    return 'border-gray-200 opacity-60'
}

const getQuestionByRef = (refStr) => {
    return QUESTIONS.find((q, idx) => q.id === refStr || (idx + 1).toString() === refStr)
}

// ==========================================
// ОСНОВНАЯ ЛОГИКА
// ==========================================

function startTest() {
    const mandatoryRefs = mandatoryInput.value.split(',').map(s => s.trim()).filter(Boolean)
    const mandatory = [...new Set(mandatoryRefs.map(ref => getQuestionByRef(ref)).filter(Boolean))]
    const mandatoryIds = new Set(mandatory.map(q => q.id))

    let available = QUESTIONS.filter(q => !mandatoryIds.has(q.id))
    let count = Math.max(0, Math.min(Number(randomCount.value) || 0, available.length))

    available.sort(() => Math.random() - 0.5)
    const random = available.slice(0, count)

    // ⭐️ СОЗДАЁМ КОПИИ ВОПРОСОВ И ПЕРЕМЕШИВАЕМ ОТВЕТЫ
    selectedQuestions.value = [...mandatory, ...random].map(q => ({
        ...q,
        responses: shuffleArray(q.responses)
    }))

    if (selectedQuestions.value.length === 0) {
        alert('Не выбрано ни одного вопроса. Проверьте параметры.')
        return
    }

    selectedQuestions.value= shuffleArray(selectedQuestions.value)
    // Инициализируем ответы как пустые массивы
    answers.value = {}
    selectedQuestions.value.forEach(q => {
        answers.value[q.id] = []
    })

    phase.value = 'test'
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function finishTest() {
    let correctCount = 0
    selectedQuestions.value.forEach(q => {
        if (isQuestionCorrect(q)) correctCount++
    })

    results.value = { correct: correctCount, total: selectedQuestions.value.length }
    phase.value = 'results'
    window.scrollTo({ top: 0, behavior: 'smooth' })
}

function resetTest() {
    phase.value = 'setup'
    randomCount.value = 1
    mandatoryInput.value = ''
    selectedQuestions.value = []
    answers.value = {}
    results.value = { correct: 0, total: 0 }
}

watch(phase, (newPhase) => {
    if (newPhase === 'test') {
        setTimeout(() => {
            const el = document.querySelector('.space-y-4')
            if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' })
        }, 100)
    }
})


const QUESTIONS = [
    {
        "id": "450192",
        "name": "Какие помещения называются сырыми?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450194",
                "name": "помещения, в которых относительная влажность воздуха превышает 60%",
                "correct": false
            },
            {
                "id": "450193",
                "name": "помещения, в которых относительная влажность воздуха не превышает 60%",
                "correct": false
            },
            {
                "id": "450196",
                "name": "помещения, в которых относительная влажность воздуха не превышает 75%",
                "correct": false
            },
            {
                "id": "450195",
                "name": "помещения, в которых относительная влажность воздуха превышает 75%",
                "correct": true
            }
        ]
    },
    {
        "id": "449962",
        "name": "На какое время, в соответствии с ПУЭ, допускается отключить электроприемники III категории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "449967",
                "name": "до устранения повреждения",
                "correct": false
            },
            {
                "id": "449965",
                "name": "до 1 суток при необходимости",
                "correct": true
            },
            {
                "id": "449963",
                "name": "на время автоматического включения резерва",
                "correct": false
            },
            {
                "id": "449964",
                "name": "до 2-х часов при необходимости",
                "correct": false
            },
            {
                "id": "449968",
                "name": "на любое",
                "correct": false
            },
            {
                "id": "449966",
                "name": "на время выезда оперативной бригады",
                "correct": false
            }
        ]
    },
    {
        "id": "450171",
        "name": "Какой тип нейтрали электросети может предусматриваться для работы электрических сетей напряжением 110 кВ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450173",
                "name": "эффективно заземленная нейтраль",
                "correct": true
            },
            {
                "id": "450177",
                "name": "нейтральная нейтраль",
                "correct": false
            },
            {
                "id": "450174",
                "name": "изолированная нейтраль",
                "correct": false
            },
            {
                "id": "450176",
                "name": "заземленная через резистор",
                "correct": false
            },
            {
                "id": "450175",
                "name": "заземленная через дугогасящий реактор",
                "correct": false
            },
            {
                "id": "450172",
                "name": "глухозаземленная нейтраль",
                "correct": true
            }
        ]
    },
    {
        "id": "450209",
        "name": "Каким должно быть наибольшее допустимое время защитного автоматического отключения для системы TN?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450214",
                "name": "для номинального фазного напряжения 380 В - 0,3 с",
                "correct": false
            },
            {
                "id": "450215",
                "name": "для номинального фазного напряжения 380 В - 0,2 с",
                "correct": true
            },
            {
                "id": "450211",
                "name": "для номинального фазного напряжения 127 В - 0,8 с",
                "correct": true
            },
            {
                "id": "450210",
                "name": "для номинального фазного напряжения 127 В – 1,0 с",
                "correct": false
            },
            {
                "id": "450212",
                "name": "для номинального фазного напряжения 220 В - 0,6 с",
                "correct": false
            },
            {
                "id": "450213",
                "name": "для номинального фазного напряжения 220 В - 0,4 с",
                "correct": true
            }
        ]
    },
    {
        "id": "437818",
        "name": "На основании каких исходных данных осуществляется подготовка проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437823",
                "name": "генеральный план муниципального образования или сельского поселения (за исключением городов Москва, Санкт-Петербург, Севастополь)",
                "correct": false
            },
            {
                "id": "437822",
                "name": "правила землепользования и застройки",
                "correct": false
            },
            {
                "id": "437820",
                "name": "информация, указанная в градостроительном плане земельного участка для нелинейных объектов",
                "correct": true
            },
            {
                "id": "437824",
                "name": "справка о фоновых концентрациях вредных веществ на территории муниципального образования или сельского поселения (за исключением городов Москва, Санкт-Петербург, Севастополь)",
                "correct": false
            },
            {
                "id": "437819",
                "name": "задание застройщика или технического заказчика (при подготовке проектной документации на основании договора подряда на подготовку проектной документации)",
                "correct": true
            },
            {
                "id": "437821",
                "name": "результаты инженерных изысканий",
                "correct": true
            }
        ]
    },
    {
        "id": "450216",
        "name": "Каким образом должно осуществляться питание электроприемников систем противопожарной защиты (СПЗ)?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450221",
                "name": "на объектах, электроприемники которых отнесены ко второй категории по надежности электроснабжения, питание электроприемников СПЗ должно осуществляться от панели ПЭСПЗ",
                "correct": false
            },
            {
                "id": "450219",
                "name": "на объектах, электроприемники которых отнесены к третьей категории по надежности электроснабжения, питание электроприемников СПЗ должно осуществляться от самостоятельного НКУ, при этом резервное питание следует осуществлять от автономного источника питания (АИП)",
                "correct": true
            },
            {
                "id": "450220",
                "name": "на объектах, электроприемники которых отнесены к первой категории по надежности электроснабжения, питание электроприемников СПЗ при отсутствии панели ПЭСПЗ на объекте защиты допускается выполнять от самостоятельного НКУ с АВР",
                "correct": true
            },
            {
                "id": "450222",
                "name": "на объектах, электроприемники которых отнесены к третьей категории по надежности электроснабжения, питание электроприемников СПЗ должно осуществляться от самостоятельного НКУ с АВР",
                "correct": false
            },
            {
                "id": "450217",
                "name": "на объектах, электроприемники которых отнесены к первой категории по надежности электроснабжения, питание электроприемников СПЗ должно осуществляться от панели ПЭСПЗ",
                "correct": true
            },
            {
                "id": "450218",
                "name": "на объектах, электроприемники которых отнесены ко второй категории по надежности электроснабжения, питание электроприемников СПЗ должно осуществляться от самостоятельного НКУ с АВР",
                "correct": true
            }
        ]
    },
    {
        "id": "437860",
        "name": "В каких случаях в состав проектной документации включается раздел \"проект организации работ по сносу или демонтажу объектов капитального строительства\"?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437861",
                "name": "при необходимости сноса частей объектов капитального строительства для реконструкции других объектов капитального строительства",
                "correct": true
            },
            {
                "id": "437864",
                "name": "при необходимости сноса аварийных объектов капитального строительства вследствие чрезвычайных ситуаций",
                "correct": false
            },
            {
                "id": "437863",
                "name": "при необходимости сноса объектов капитального строительства для строительства других объектов капитального строительства",
                "correct": true
            },
            {
                "id": "437865",
                "name": "при демонтаже подъемных машин и механизмов",
                "correct": false
            },
            {
                "id": "437866",
                "name": "при сносе объектов капитального строительства вследствие сейсмических воздействий",
                "correct": false
            },
            {
                "id": "437862",
                "name": "при необходимости сноса или демонтажа временных зданий и сооружений, расположенных на земельном участке, предоставленном для строительства другого объекта капитального строительства",
                "correct": false
            }
        ]
    },
    {
        "id": "438130",
        "name": "В отношении проектной документации и результатов инженерных изысканий следующих объектов капитального строительства по решению застройщика допускается проведение негосударственной экспертизы: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438134",
                "name": "объектов обезвреживания отходов",
                "correct": false
            },
            {
                "id": "438133",
                "name": "объектов, строительство которых планируется осуществлять в границах охранных зон трубопроводов",
                "correct": true
            },
            {
                "id": "438135",
                "name": "опасных производственных объектов VI класса опасности",
                "correct": true
            },
            {
                "id": "438132",
                "name": "объектов, строительство которых планируется в границах охранной зоны железных дорог",
                "correct": true
            },
            {
                "id": "438136",
                "name": "объектов муниципальной собственности",
                "correct": false
            },
            {
                "id": "438131",
                "name": "объектов, строительство которых планируется на землях государственных природных заповедников и национальных парков",
                "correct": false
            }
        ]
    },
    {
        "id": "450164",
        "name": "Каким должно быть расстояние по вертикали между проводами пересекающихся ВЛ (ВЛИ) напряжением до 1 кВ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450170",
                "name": "не менее 1,5 м в пролете",
                "correct": false
            },
            {
                "id": "450166",
                "name": "не менее 0,2 м на опоре",
                "correct": false
            },
            {
                "id": "450165",
                "name": "не менее 0,1 м на опоре ",
                "correct": true
            },
            {
                "id": "450169",
                "name": "не менее 1,0 м в пролете",
                "correct": true
            },
            {
                "id": "450167",
                "name": "не менее 0,3 м на опоре",
                "correct": false
            },
            {
                "id": "450168",
                "name": "не менее 0,5 м в пролете",
                "correct": false
            }
        ]
    },
    {
        "id": "450377",
        "name": "Какие электроустановки относятся к закрытым или внутренним в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450380",
                "name": "электроустановки, защищенные от атмосферных воздействий навесами, сетчатыми ограждениями и т.п.",
                "correct": false
            },
            {
                "id": "450379",
                "name": "электроустановки, размещенные внутри зданий, защищающих их от атмосферных воздействий",
                "correct": true
            },
            {
                "id": "450378",
                "name": "электроустановки, размещенные внутри здания, защищающего их от атмосферных воздействий, а также электроустановки, защищенные навесами, сетчатыми ограждениями и т.п",
                "correct": false
            },
            {
                "id": "450381",
                "name": "любые электроустановки на внутренней территории предприятия, защищенные и не защищенные от атмосферных воздействий",
                "correct": false
            }
        ]
    },
    {
        "id": "437934",
        "name": "На какие виды подразделяются объекты капитального строительства в зависимости от функционального назначения и характерных признаков?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437935",
                "name": "гидротехнические объекты",
                "correct": false
            },
            {
                "id": "437940",
                "name": "уникальные",
                "correct": false
            },
            {
                "id": "437938",
                "name": "линейные объекты",
                "correct": true
            },
            {
                "id": "437939",
                "name": "объекты непроизводственного назначения",
                "correct": true
            },
            {
                "id": "437937",
                "name": "объекты обороны",
                "correct": false
            },
            {
                "id": "437936",
                "name": "объекты производственного назначения",
                "correct": true
            }
        ]
    },
    {
        "id": "450343",
        "name": "Выбор способов прокладки силовых кабельных линии до 35 кВ:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450347",
                "name": "При выборе способов прокладки кабелей по территориям городов должны учитываться первоначальные капитальные затраты и затраты, связанные с производством эксплуатационно - ремонтных работ, а также удобство и экономичность облуживания сооружений",
                "correct": true
            },
            {
                "id": "450345",
                "name": "Прокладка кабелей в туннелях, по эстакадам и в галереях рекомендуется при количестве силовых кабелей, идущих в одном направлении, более 20",
                "correct": true
            },
            {
                "id": "450344",
                "name": "При прокладке кабелей в земле рекомендуется в одной траншее прокладывать не более шести силовых кабелей. При большом количестве кабелей рекомендуется прокладывать их в отдельных траншеях с расстоянием между группами кабелей не менее 0,5 м или в каналах, туннелях, по эстакадам и в галереях",
                "correct": true
            },
            {
                "id": "450348",
                "name": "При прокладке кабелей в земле рекомендуется в одной траншее прокладывать не более шести силовых кабелей. При большом количестве кабелей рекомендуется прокладывать их в отдельных траншеях с расстоянием между группами кабелей не менее 1 м или в каналах, туннелях, по эстакадам и в галереях",
                "correct": false
            },
            {
                "id": "450346",
                "name": "Прокладка кабелей в блоках применяется в условиях большой стесненности по трассе, в местах пересечений с железнодорожными путями и проездами, при вероятности разлива металла и т.п",
                "correct": true
            },
            {
                "id": "450349",
                "name": "Прокладка кабелей в туннелях, по эстакадам и в галереях рекомендуется при количестве силовых кабелей, идущих в одном направлении, более 10",
                "correct": false
            }
        ]
    },
    {
        "id": "450363",
        "name": "При каких видах КЗ аппараты защиты должны обеспечивать отключение поврежденного участка при КЗ в конце защищаемой линии в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450364",
                "name": "однофазном КЗ в сетях с глухозаземленной нейтралью",
                "correct": true
            },
            {
                "id": "450367",
                "name": "однофазном КЗ в сетях с изолированной нейтралью",
                "correct": false
            },
            {
                "id": "450368",
                "name": "двухфазном КЗ в сетях с изолированной нейтралью",
                "correct": true
            },
            {
                "id": "450366",
                "name": "трёхфазном КЗ в сетях с глухозаземленной нейтралью",
                "correct": true
            },
            {
                "id": "450369",
                "name": "трёхфазном КЗ в сетях с изолированной нейтралью",
                "correct": true
            },
            {
                "id": "450365",
                "name": "двухфазном КЗ в сетях с глухозаземленной нейтралью",
                "correct": true
            }
        ]
    },
    {
        "id": "437493",
        "name": "Какие сведения включаются в подраздел единого государственного реестра заключений экспертизы проектной документации объектов капитального строительства, касающийся сведений о представленных для проведения экспертизы результатах инженерных изысканий?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437499",
                "name": "сведения об индивидуальных предпринимателях и (или) юридических лицах, подготовивших технический отчет по результатам инженерных изысканий",
                "correct": true
            },
            {
                "id": "437497",
                "name": "сведения о застройщике (техническом заказчике), обеспечившем проведение инженерных изысканий",
                "correct": true
            },
            {
                "id": "437494",
                "name": "сведения о местоположении района (площадки, трассы) проведения изысканий (субъект Российской Федерации, муниципальный район)",
                "correct": true
            },
            {
                "id": "437496",
                "name": "дата подготовки отчета по результатам инженерных изысканий",
                "correct": true
            },
            {
                "id": "437498",
                "name": "реквизиты (номер, дата выдачи) положительного заключения экспертизы в отношении применяемой типовой проектной документации (в случае, если для проведения экспертизы результатов инженерных изысканий требуется представление такого заключения)",
                "correct": false
            },
            {
                "id": "437495",
                "name": "виды работ по инженерным изысканиям",
                "correct": true
            }
        ]
    },
    {
        "id": "450337",
        "name": "На какой высоте устанавливается аппараты для подключения электроприемников до 1 кВ на опорах?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450339",
                "name": "Не менее 1,8 м от поверхности земли. Устанавливаемые на опорах защитные секционирующие устройства должны размещаться ниже проводов ВЛ",
                "correct": false
            },
            {
                "id": "450338",
                "name": "Не менее 1,6 м от поверхности земли. Устанавливаемые на опорах защитные секционирующие устройства должны размещаться ниже проводов ВЛ",
                "correct": true
            },
            {
                "id": "450340",
                "name": "Не менее 2,6 м от поверхности земли. Устанавливаемые на опорах защитные секционирующие устройства должны размещаться ниже проводов ВЛ",
                "correct": false
            },
            {
                "id": "450342",
                "name": "Не менее 1,5 м от поверхности земли. Устанавливаемые на опорах защитные секционирующие устройства должны размещаться ниже проводов ВЛ",
                "correct": false
            },
            {
                "id": "450341",
                "name": "Не менее 0,6 м от поверхности земли. Устанавливаемые на опорах защитные секционирующие устройства должны размещаться ниже проводов ВЛ",
                "correct": false
            }
        ]
    },
    {
        "id": "437981",
        "name": "Какие разделы проектной документации подготавливаются в виде файлов в формате xml для представления их на экспертизу?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437985",
                "name": "разделы, xml-схема которых утверждена Правительством Российской Федерации и введена в действие",
                "correct": false
            },
            {
                "id": "437984",
                "name": "разделы, xml-схема которых размещена на официальном сайте Минстроя России и введена в действие ",
                "correct": true
            },
            {
                "id": "437986",
                "name": "проект организации строительства",
                "correct": false
            },
            {
                "id": "437982",
                "name": "пояснительная записка, xml-схема которой размещена на официальном сайте Минстроя России и введена в действие",
                "correct": true
            },
            {
                "id": "437983",
                "name": "разделы, xml-схема которых размещена на официальном сайте ФАУ \"Главгосэкспертиза России\" и введена в действие ",
                "correct": false
            }
        ]
    },
    {
        "id": "450050",
        "name": "Разрешается ли скрытая установка по одной оси розеток, выключателей в общих стенах разных квартир?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450051",
                "name": "разрешается",
                "correct": false
            },
            {
                "id": "450054",
                "name": "разрешается на высоте 1,8 м",
                "correct": false
            },
            {
                "id": "450053",
                "name": "разрешается при толщине стены более 15 см",
                "correct": false
            },
            {
                "id": "450052",
                "name": "не разрешаются",
                "correct": true
            }
        ]
    },
    {
        "id": "437960",
        "name": "В отношении каких объектов предусмотрены особенности состава разделов проектной документации и требований к их содержанию?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437964",
                "name": "атомных станций",
                "correct": true
            },
            {
                "id": "437963",
                "name": "уникальных",
                "correct": false
            },
            {
                "id": "437966",
                "name": "объектов повышенного уровня ответственности",
                "correct": false
            },
            {
                "id": "437962",
                "name": "культурного наследия",
                "correct": false
            },
            {
                "id": "437961",
                "name": "метрополитена",
                "correct": true
            },
            {
                "id": "437965",
                "name": "предприятий по добыче и первичной переработке твердых полезных ископаемых",
                "correct": true
            }
        ]
    },
    {
        "id": "450262",
        "name": "Для каких общественных зданий значение коэффициента мощности для расчета силовых сетей принимается равным 0,9?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450268",
                "name": "для гостиниц с ресторанами",
                "correct": true
            },
            {
                "id": "450265",
                "name": "для общеобразовательных школ с пищеблоками",
                "correct": false
            },
            {
                "id": "450266",
                "name": "для общеобразовательных школ без пищеблоков",
                "correct": true
            },
            {
                "id": "450263",
                "name": "для полностью электрифицированных предприятий общественного питания",
                "correct": false
            },
            {
                "id": "450264",
                "name": "для яслей-садов с пищеблоками",
                "correct": false
            },
            {
                "id": "450267",
                "name": "для учебных корпусов учреждений профессионального образования",
                "correct": true
            }
        ]
    },
    {
        "id": "450530",
        "name": "Какие обозначения приняты для электроустановок напряжением до 1 кВ, в соответствии с ПУЭ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450532",
                "name": "система TN-C",
                "correct": true
            },
            {
                "id": "450536",
                "name": "система TT",
                "correct": true
            },
            {
                "id": "450531",
                "name": "система ТN",
                "correct": true
            },
            {
                "id": "450533",
                "name": "система TN-S",
                "correct": true
            },
            {
                "id": "450534",
                "name": "система TN-C-S",
                "correct": true
            },
            {
                "id": "450535",
                "name": "система IT",
                "correct": true
            }
        ]
    },
    {
        "id": "450415",
        "name": "Случаи, в которых не требуется защита от прямого прикосновения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450417",
                "name": "во всех случаях, если напряжение в электроустановке не превышает 24 В переменного и 90 В постоянного тока",
                "correct": false
            },
            {
                "id": "450419",
                "name": "если электрооборудование находится в зоне системы уравнивания потенциалов, а наибольшее рабочее напряжение не превышает 50 В переменного или 90 В постоянного тока во всех случаях",
                "correct": false
            },
            {
                "id": "450418",
                "name": "во всех случаях, если напряжение в электроустановке не превышает 50 В переменного и 80 В постоянного тока",
                "correct": false
            },
            {
                "id": "450416",
                "name": "защита от прямого прикосновения не требуется, если электрооборудование находится в зоне системы уравнивания потенциалов, а наибольшее рабочее напряжение не превышает 25 В переменного или 60 В постоянного тока в помещениях без повышенной опасности и 6 В переменного или 15 В постоянного тока - во всех случаях",
                "correct": true
            }
        ]
    },
    {
        "id": "437685",
        "name": "Что понимается под определениями «сертификация» и «система сертификации»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437686",
                "name": "сертификация - форма осуществляемого органом по сертификации подтверждения соответствия объектов требованиям технических регламентов, документам по стандартизации или условиям договоров",
                "correct": true
            },
            {
                "id": "437688",
                "name": "сертификация - установление тождественности характеристик продукции её существующим признакам",
                "correct": false
            },
            {
                "id": "437689",
                "name": "система сертификации - форма подтверждения соответствия продукции требованиям технических регламентов",
                "correct": false
            },
            {
                "id": "437687",
                "name": "сертификация - прямое или косвенное определение соблюдения требований, предъявляемых к объекту",
                "correct": false
            },
            {
                "id": "437691",
                "name": "система сертификации - проверка выполнения юридическим лицом или индивидуальным предпринимателем требований технических регламентов к продукции или к продукции и связанным с требованиями к продукции процессам проектирования (включая изыскания), производства, строительства, монтажа, наладки, эксплуатации, хранения, перевозки, реализации и утилизации и принятие мер по результатам проверки",
                "correct": false
            },
            {
                "id": "437690",
                "name": "система сертификации - совокупность правил выполнения работ по сертификации, ее участников и правил функционирования системы сертификации в целом",
                "correct": true
            }
        ]
    },
    {
        "id": "437472",
        "name": "Какие сведения должно содержать заключение в отношении подписавших его экспертов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437473",
                "name": "фамилия, имя, отчество (при наличии) эксперта",
                "correct": true
            },
            {
                "id": "437475",
                "name": "направление деятельности эксперта",
                "correct": true
            },
            {
                "id": "437476",
                "name": "номер квалификационного аттестата на право подготовки заключений экспертизы эксперта",
                "correct": true
            },
            {
                "id": "437478",
                "name": "наименование и реквизиты документа, являющегося основанием для привлечения эксперта к подготовке заключения экспертизы",
                "correct": false
            },
            {
                "id": "437474",
                "name": "должность эксперта (экспертов), проводившего экспертизу",
                "correct": false
            },
            {
                "id": "437477",
                "name": "дата выдачи и дата окончания срока действия квалификационного аттестата на право подготовки заключений экспертизы эксперта",
                "correct": true
            }
        ]
    },
    {
        "id": "450558",
        "name": "Чем должны быть оборудованы электрощитовые в жилых или общественных зданиях? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450559",
                "name": "естественной вентиляцией",
                "correct": true
            },
            {
                "id": "450562",
                "name": "температура должна быть не ниже 5 °С",
                "correct": true
            },
            {
                "id": "450563",
                "name": "температура должна быть не ниже 15 °С",
                "correct": false
            },
            {
                "id": "450564",
                "name": "водоснабжением",
                "correct": false
            },
            {
                "id": "450560",
                "name": "электрическим освещением",
                "correct": true
            },
            {
                "id": "450561",
                "name": "аварийным освещением",
                "correct": true
            }
        ]
    },
    {
        "id": "437341",
        "name": "Какие объекты инфраструктуры воздушного транспорта, являющиеся особо опасными, технически сложными объектами, должны быть идентифицированы как объекты повышенного уровня ответственности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437345",
                "name": "аэровокзалы (терминалы) пропускной способностью 100 пассажиров в час и более",
                "correct": true
            },
            {
                "id": "437344",
                "name": "места стоянок воздушных судов и перроны аэродромов с искусственным покрытием с длиной взлетно-посадочной полосы 1300 м и более",
                "correct": true
            },
            {
                "id": "437342",
                "name": "взлетно-посадочные полосы",
                "correct": true
            },
            {
                "id": "437343",
                "name": "рулежные дорожки",
                "correct": true
            },
            {
                "id": "437347",
                "name": "командно-диспетчерские и стартовые диспетчерские пункты высотой более трех этажей или площадью 1500 кв. м и более",
                "correct": true
            },
            {
                "id": "437346",
                "name": "командно-диспетчерские и стартовые диспетчерские пункты модульного (контейнерного) типа",
                "correct": false
            }
        ]
    },
    {
        "id": "450551",
        "name": "Какое расстояние для ВЛ напряжением до 1 кВ от проводов до поверхности земли должно быть на ответвлениях к вводу, в соответствии с ПУЭ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450555",
                "name": "расстояние на ответвлениях к вводу должно быть не менее 2,75 м",
                "correct": false
            },
            {
                "id": "450552",
                "name": "расстояние от СИП и изолированных проводов до поверхности земли на ответвлениях к вводу должно быть не менее 2,5 м",
                "correct": true
            },
            {
                "id": "450556",
                "name": "расстояние на ответвлениях к вводу не нормируется ",
                "correct": false
            },
            {
                "id": "450554",
                "name": "расстояние на ответвлениях к вводу должно быть не менее 2,5 м",
                "correct": false
            },
            {
                "id": "450553",
                "name": "расстояние от неизолированных проводов до поверхности земли на ответвлениях к вводам должно быть не менее 2,75 м",
                "correct": true
            },
            {
                "id": "450557",
                "name": "расстояние от неизолированных проводов и изолированных проводов до поверхности земли на ответвлениях к вводам должно быть не менее 3,0 м",
                "correct": false
            }
        ]
    },
    {
        "id": "450138",
        "name": "Какая предусматривается средняя освещенность тротуаров, отделенных от проезжей части дорог и улиц, основных проездов микрорайонов, подъездов, подходов и центральных аллеи детских, учебных и лечебно-оздоровительных учреждений?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450141",
                "name": "не менее 6 лк",
                "correct": false
            },
            {
                "id": "450139",
                "name": "не менее 20 лк",
                "correct": false
            },
            {
                "id": "450140",
                "name": "не менее 10 лк",
                "correct": false
            },
            {
                "id": "450143",
                "name": "не менее 2 лк",
                "correct": false
            },
            {
                "id": "450142",
                "name": "не менее 4 лк",
                "correct": true
            }
        ]
    },
    {
        "id": "450030",
        "name": "Допускается ли в жилых зданиях прокладка вертикальных участков (стояков) распределительной сети внутри квартир?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450033",
                "name": "допускается в трубе, не распространяющей горение",
                "correct": false
            },
            {
                "id": "450034",
                "name": "допускается в случае применения шинопроводов",
                "correct": false
            },
            {
                "id": "450032",
                "name": "допускается",
                "correct": false
            },
            {
                "id": "450031",
                "name": "не допускается",
                "correct": true
            }
        ]
    },
    {
        "id": "437873",
        "name": "По чьей инициативе подготовка проектной документации может осуществляться применительно к отдельным этапам строительства, реконструкции объектов капитального строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437879",
                "name": "регионального оператора",
                "correct": false
            },
            {
                "id": "437876",
                "name": "лица, осуществляющего эксплуатацию объекта капитального строительства",
                "correct": false
            },
            {
                "id": "437874",
                "name": "застройщика",
                "correct": true
            },
            {
                "id": "437875",
                "name": "технического заказчика",
                "correct": true
            },
            {
                "id": "437877",
                "name": "лица, осуществляющего подготовку проектной документации",
                "correct": false
            },
            {
                "id": "437878",
                "name": "лица, осуществляющего строительство",
                "correct": false
            }
        ]
    },
    {
        "id": "450491",
        "name": "Сколько масляных или заполненных негорючим экологически безопасным жидким диэлектриком трансформаторов следует устанавливать на встроенных ТП и КТП в жилых или общественных зданиях?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450496",
                "name": "не более трёх трансформаторов мощностью до 1600 кВА",
                "correct": false
            },
            {
                "id": "450492",
                "name": "не более двух трансформаторов мощностью до 2500 кВА",
                "correct": false
            },
            {
                "id": "450495",
                "name": "не более двух трансформаторов мощностью до 1600 кВА",
                "correct": false
            },
            {
                "id": "450493",
                "name": "не более трёх трансформаторов мощностью до 1000 кВА",
                "correct": false
            },
            {
                "id": "450494",
                "name": "не более двух трансформаторов мощностью до 1000 кВА",
                "correct": true
            }
        ]
    },
    {
        "id": "437375",
        "name": "В течение скольких дней со дня получения от заявителя документов организация по проведению государственной экспертизы может осуществлять их проверку?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437380",
                "name": "не более 10 календарных дней в отношении объектов, строительство, реконструкция которых предполагается осуществлять на континентальном шельфе Российской Федерации",
                "correct": false
            },
            {
                "id": "437381",
                "name": "в течение срока, установленного договором",
                "correct": false
            },
            {
                "id": "437379",
                "name": "в течение 3 календарных дней со дня получения от заявителя требуемых документов",
                "correct": false
            },
            {
                "id": "437376",
                "name": "в течение 3 рабочих дней со дня получения от заявителя требуемых документов",
                "correct": true
            },
            {
                "id": "437378",
                "name": "не более 10 рабочих дней в отношении объектов индивидуального жилищного строительства",
                "correct": false
            },
            {
                "id": "437377",
                "name": "не более 10 рабочих дней в отношении объектов, строительство, реконструкцию которых предполагается осуществлять на территориях посольств, консульств и представительств Российской Федерации за рубежом",
                "correct": true
            }
        ]
    },
    {
        "id": "450690",
        "name": "Электрические сети жилых и административных зданий, прокладываемые за подвесными потолками и в пустотах перегородок, в соответствии с ПУЭ, следует выполнять: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450691",
                "name": "за подвесными потолками и в пустотах перегородок из горючих материалов – в выполненных из негорючих материалов трубах и коробах ",
                "correct": false
            },
            {
                "id": "450695",
                "name": "за подвесными потолками и в пустотах перегородок из негорючих материалов – кабелями, не распространяющими горение",
                "correct": true
            },
            {
                "id": "450692",
                "name": "за подвесными потолками и в пустотах перегородок из горючих материалов – в металлических трубах, обладающих локализационной способностью и в закрытых коробах",
                "correct": true
            },
            {
                "id": "450694",
                "name": "за подвесными потолками и в пустотах перегородок из негорючих материалов – в выполненных из негорючих материалов трубах и коробах",
                "correct": true
            },
            {
                "id": "450693",
                "name": "за подвесными потолками и в пустотах перегородок из горючих материалов – кабелями, не распространяющими горение ",
                "correct": false
            }
        ]
    },
    {
        "id": "450648",
        "name": "Каким должно быть расстояние по вертикали от проводов ВЛ до поверхности земли в населенной местности в нормальном режиме в соответствии с ПУЭ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450650",
                "name": "ВЛЗ – не менее 6 м",
                "correct": true
            },
            {
                "id": "450649",
                "name": "ВЛЗ – не менее 5 м",
                "correct": false
            },
            {
                "id": "450651",
                "name": "ВЛ до 35 кВ – не менее 6 м",
                "correct": false
            },
            {
                "id": "450652",
                "name": "ВЛ до 35 кВ – не менее 7 м",
                "correct": true
            },
            {
                "id": "450654",
                "name": "ВЛ 110 кВ – не менее 8 м",
                "correct": false
            },
            {
                "id": "450653",
                "name": "ВЛ 110 кВ – не менее 7 м",
                "correct": true
            }
        ]
    },
    {
        "id": "450230",
        "name": "Какое расстояние должно быть от проводов ВЛ до 1 кВ в населенной и ненаселенной местности при наибольшей стреле провеса проводов до земли и проезжей части улиц? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450235",
                "name": "не менее 8 метров",
                "correct": false
            },
            {
                "id": "450236",
                "name": "не менее 9 метров",
                "correct": false
            },
            {
                "id": "450231",
                "name": "не менее 4 метра",
                "correct": false
            },
            {
                "id": "450232",
                "name": "не менее 5 метров",
                "correct": false
            },
            {
                "id": "450233",
                "name": "не менее 6 метров",
                "correct": true
            },
            {
                "id": "450234",
                "name": "не менее 7 метров",
                "correct": false
            }
        ]
    },
    {
        "id": "450525",
        "name": "Какое должно быть минимально допустимое расстояние между взаимно резервирующими кабелями при прокладке в земле, в соответствии в СП 76.13330.2016? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450529",
                "name": "расстояние не нормируется",
                "correct": false
            },
            {
                "id": "450528",
                "name": "500 мм",
                "correct": false
            },
            {
                "id": "450527",
                "name": "100 мм",
                "correct": false
            },
            {
                "id": "450526",
                "name": " 1 м",
                "correct": true
            }
        ]
    },
    {
        "id": "438084",
        "name": "Какие факторы определяют необходимость проведения в отношении проектной документации государственной экспертизы?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438087",
                "name": "планируется строительство гидротехнического сооружения третьего класса ответственности",
                "correct": false
            },
            {
                "id": "438085",
                "name": "строительство объекта планируется в границах зон с особыми условиями использования территорий, режим которых предусматривает ограничение размещения объектов капитального строительства исходя из оценки их влияния на объект, территорию, в целях охраны которых установлена зона с особыми условиями использования территории, или исходя из оценки влияния объекта, территории, в целях охраны которых установлена зона с особыми условиями использования территории, на размещаемый объект капитального строительства",
                "correct": false
            },
            {
                "id": "438089",
                "name": "проектируемые объекты относятся к объектам космической инфраструктуры",
                "correct": true
            },
            {
                "id": "438090",
                "name": "предусмотрено наличие конструкций, в отношении которых применяются нестандартные методы расчета с учетом физических или геометрических нелинейных свойств",
                "correct": false
            },
            {
                "id": "438086",
                "name": "планируется строительство линии электропередач или иных объектов электросетевого хозяйства мощностью 30 МВт и более",
                "correct": false
            },
            {
                "id": "438088",
                "name": "предусмотрено заглубление подземной части (полностью или частично) ниже планировочной отметки земли более чем на 15 метров\n",
                "correct": true
            }
        ]
    },
    {
        "id": "450158",
        "name": "Каким должно быть расстояние по вертикали от проводов ВЛИ напряжением до 1 кВ до поверхности земли в населенной и ненаселенной местности до земли и проезжей части улиц?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450161",
                "name": "не менее 4 м",
                "correct": false
            },
            {
                "id": "450162",
                "name": "не менее 2,5 м в труднодоступной местности",
                "correct": true
            },
            {
                "id": "450159",
                "name": "не менее 7 м",
                "correct": false
            },
            {
                "id": "450163",
                "name": "не менее 2 м в труднодоступной местности",
                "correct": false
            },
            {
                "id": "450160",
                "name": "не менее 5 м",
                "correct": true
            }
        ]
    },
    {
        "id": "437825",
        "name": "Подготовка каких объектов капитального строительства может осуществляться на основании проекта планировки территории и проекта межевания территории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437828",
                "name": "линии электропередачи",
                "correct": true
            },
            {
                "id": "437826",
                "name": "гидротехнические сооружения",
                "correct": false
            },
            {
                "id": "437831",
                "name": "линии метрополитена",
                "correct": true
            },
            {
                "id": "437827",
                "name": "опасные производственные объекты",
                "correct": false
            },
            {
                "id": "437829",
                "name": "линейно-кабельные сооружения",
                "correct": true
            },
            {
                "id": "437830",
                "name": "трубопроводы",
                "correct": true
            }
        ]
    },
    {
        "id": "438118",
        "name": "При проведении повторной экспертизы экспертной оценке подлежит: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438121",
                "name": "проектная документация и результаты инженерных изысканий в полном объеме в случае, если для проведения первичной экспертизы проектная документация и результаты инженерных изысканий представлялись на бумажном носителе",
                "correct": false
            },
            {
                "id": "438123",
                "name": "результаты инженерных изысканий в полном объеме в случае, если после проведения первичной экспертизы таких результатов инженерных изысканий в законодательство внесены изменения, в соответствии с которыми экспертиза должна осуществляться иной организацией по проведению экспертизы",
                "correct": true
            },
            {
                "id": "438120",
                "name": "часть проектной документации и (или) результатов инженерных изысканий, в которые были внесены изменения, а также совместимость внесенных изменений с проектной документацией и (или) результатами инженерных изысканий, в отношении которых была ранее проведена экспертиза, по результатам которой было выдано отрицательное заключение экспертизы",
                "correct": true
            },
            {
                "id": "438119",
                "name": "часть проектной документации и (или) результатов инженерных изысканий, в которые были внесены изменения, а также совместимость внесенных изменений с проектной документацией и (или) результатами инженерных изысканий, в отношении которых была ранее проведена экспертиза, по результатам которой было выдано положительное заключение экспертизы",
                "correct": true
            },
            {
                "id": "438122",
                "name": "проектная документация в полном объеме в случае, если после проведения первичной экспертизы такой проектной документации в законодательство внесены изменения, в соответствии с которыми экспертиза должна осуществляться иной организацией по проведению экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "450223",
        "name": "К какой степени загрязнения могут быть отнесены районы (территории), находящиеся вблизи заводов синтетического каучука, при расстоянии от источника загрязнения до 1000 м?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450227",
                "name": "к 4-й степени загрязнения",
                "correct": false
            },
            {
                "id": "450225",
                "name": "ко 2-й степени загрязнения",
                "correct": true
            },
            {
                "id": "450224",
                "name": "к 1-й степени загрязнения",
                "correct": true
            },
            {
                "id": "450228",
                "name": "только к 3-й степени загрязнения",
                "correct": false
            },
            {
                "id": "450229",
                "name": "только к 4-й степени загрязнения",
                "correct": false
            },
            {
                "id": "450226",
                "name": "к 3-й степени загрязнения",
                "correct": true
            }
        ]
    },
    {
        "id": "450620",
        "name": "Сколько соединительных муфт должно быть на 1 км вновь строящихся кабельных линий в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450625",
                "name": "не более 4 шт. для одножильных кабелей",
                "correct": false
            },
            {
                "id": "450623",
                "name": "не более 5 шт. для трёхжильных кабелей 1-10 кВ сечением от 3х120 мм2 до 3х240 мм2",
                "correct": true
            },
            {
                "id": "450622",
                "name": "не более 4 шт. для трёхжильных кабелей 1-10 кВ сечением до 3х95 мм2",
                "correct": true
            },
            {
                "id": "450621",
                "name": "не более 2 шт. для трёхжильных кабелей 1-10 кВ сечением до 3х95 мм2",
                "correct": false
            },
            {
                "id": "450624",
                "name": "не более 6 шт. для трёхфазных кабелей 10-35 кВ ",
                "correct": true
            },
            {
                "id": "450626",
                "name": "не более 2 шт. для одножильных кабелей",
                "correct": true
            }
        ]
    },
    {
        "id": "437618",
        "name": "Какие объекты относятся к объектам капитального строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437620",
                "name": "здание",
                "correct": true
            },
            {
                "id": "437622",
                "name": "строение",
                "correct": true
            },
            {
                "id": "437619",
                "name": "некапитальные сооружения",
                "correct": false
            },
            {
                "id": "437624",
                "name": "сооружение",
                "correct": true
            },
            {
                "id": "437623",
                "name": "неотделимые улучшения земельного участка",
                "correct": false
            },
            {
                "id": "437621",
                "name": "объекты, строительство которых не завершено",
                "correct": true
            }
        ]
    },
    {
        "id": "437431",
        "name": "Какие действия вправе предпринимать при проведении государственной экспертизы организация по проведению государственной экспертизы?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437435",
                "name": "истребовать от органов государственной власти сведения и документы, необходимые для проведения государственной экспертизы",
                "correct": true
            },
            {
                "id": "437432",
                "name": "привлекать на договорной основе к проведению государственной экспертизы государственные организации",
                "correct": true
            },
            {
                "id": "437433",
                "name": "привлекать на договорной основе к проведению государственной экспертизы негосударственные организации",
                "correct": true
            },
            {
                "id": "437434",
                "name": "привлекать на договорной основе к проведению государственной экспертизы специалистов",
                "correct": true
            },
            {
                "id": "437437",
                "name": "истребовать от организаций сведения и документы, необходимые для проведения государственной экспертизы",
                "correct": true
            },
            {
                "id": "437436",
                "name": "редактировать и удалять документы, необходимые для проведения государственной экспертизы, представленные в электронной форме",
                "correct": false
            }
        ]
    },
    {
        "id": "437790",
        "name": "Какие материалы должны быть предоставлены лицу, осуществляющему подготовку проектной документации на основании договора подряда, застройщиком?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437795",
                "name": "технические условия подключения (в случае, если функционирование проектируемого объекта капитального строительства невозможно обеспечить без подключения такого объекта к сетям инженерно-технического обеспечения)",
                "correct": true
            },
            {
                "id": "437792",
                "name": "результаты инженерных изысканий (в случае, если они отсутствуют, договором подряда на подготовку проектной документации должно быть предусмотрено задание на выполнение инженерных изысканий)",
                "correct": true
            },
            {
                "id": "437791",
                "name": "градостроительный план земельного участка для нелинейного объекта",
                "correct": true
            },
            {
                "id": "437796",
                "name": "перечень технологического оборудования с указанием нагрузок и условий присоединения к сетям инженерно-технического обеспечения",
                "correct": false
            },
            {
                "id": "437794",
                "name": "контролируемые климатические параметры района строительства",
                "correct": false
            },
            {
                "id": "437793",
                "name": "календарный график работ",
                "correct": false
            }
        ]
    },
    {
        "id": "449997",
        "name": "Каким должно быть сопротивление заземляющего устройства, к которому присоединяется нейтраль стационарной дизельной электростанции с линейным напряжением 380 В?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "449999",
                "name": "8 Ом",
                "correct": false
            },
            {
                "id": "450002",
                "name": "60 Ом",
                "correct": false
            },
            {
                "id": "450000",
                "name": "не менее 10 Ом",
                "correct": false
            },
            {
                "id": "449998",
                "name": "не более 4 Ом",
                "correct": true
            },
            {
                "id": "450001",
                "name": "30 Ом",
                "correct": false
            },
            {
                "id": "450003",
                "name": "не менее 4 Ом",
                "correct": false
            }
        ]
    },
    {
        "id": "437899",
        "name": "Какие мероприятия должна содержать проектная документация опасных производственных объектов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437904",
                "name": "перечень мероприятий по обеспечению доступа инвалидов",
                "correct": false
            },
            {
                "id": "437902",
                "name": "перечень мероприятий по защите от грызунов (по дератизации)",
                "correct": false
            },
            {
                "id": "437905",
                "name": "перечень мероприятий по удалению избытков грунта",
                "correct": false
            },
            {
                "id": "437901",
                "name": "перечень мероприятий по противодействию терроризму",
                "correct": true
            },
            {
                "id": "437900",
                "name": "перечень мероприятий по гражданской обороне",
                "correct": true
            },
            {
                "id": "437903",
                "name": "перечень мероприятий по предупреждению чрезвычайных ситуаций природного и техногенного характера",
                "correct": true
            }
        ]
    },
    {
        "id": "438105",
        "name": "В каком случае в отношении изменений, внесенных в проектную документацию после выдачи положительного заключения экспертизы, не требуется проведение повторной экспертизы (экспертного сопровождения)? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438111",
                "name": "В случае если в результате изменений, внесенных в проектную документацию, стоимость строительства (реконструкции) объекта капитального строительства, осуществляемого за счет средств бюджетов бюджетной системы Российской Федерации, меньше стоимости строительства, установленной в решении о предоставлении бюджетных ассигнований на осуществление капитальных вложений, принятом в отношении объекта капитального строительства государственной (муниципальной) собственности в установленном порядке",
                "correct": false
            },
            {
                "id": "438107",
                "name": "В случае если не изменялись физические объемы работ, конструктивные, организационно-технологические и другие решения, предусмотренные проектной документацией, и изменения соответствуют установленной в решении о предоставлении бюджетных ассигнований на осуществление капитальных вложений, принятом в отношении объекта капитального строительства государственной (муниципальной) собственности в установленном порядке, стоимости строительства (реконструкции) объекта капитального строительства, осуществляемого за счет средств бюджетов бюджетной системы Российской Федерации",
                "correct": true
            },
            {
                "id": "438109",
                "name": "В случае если изменения, внесенные в проектную документацию, утверждены специалистом по организации архитектурно-строительного проектирования в должности главного инженера проекта",
                "correct": false
            },
            {
                "id": "438108",
                "name": "В случае если застройщик (технический заказчик) утвердил изменения, внесенные в проектную документацию, при наличии подтверждения соответствия вносимых в проектную документацию изменений требованиям, указанным в части 3.8 статьи 49 Градостроительного кодекса Российской Федерации, предоставленного лицом, являющимся членом саморегулируемой организации, основанной на членстве лиц, осуществляющих подготовку проектной документации, утвержденного специалистом по организации архитектурно-строительного проектирования в должности главного инженера проекта",
                "correct": true
            },
            {
                "id": "438110",
                "name": "В случае если изменения, внесенные в проектную документацию, не влекут за собой изменение класса, но влекут изменение первоначально установленных показателей функционирования линейного объекта.",
                "correct": false
            },
            {
                "id": "438106",
                "name": "В случае если предусмотрены изменения только в части замены всех видов несущих строительных конструкций объекта капитального строительства на аналогичные или иные улучшающие показатели конструкции",
                "correct": false
            }
        ]
    },
    {
        "id": "437967",
        "name": "Что входит в состав исходных данных, подлежащих включению в раздел «пояснительная записка» для объектов производственного и непроизводственного назначения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437972",
                "name": "задание на проектирование",
                "correct": true
            },
            {
                "id": "437973",
                "name": "выданный в установленном порядке градостроительный план земельного участка, предназначенный для размещения объекта капитального строительства",
                "correct": true
            },
            {
                "id": "437968",
                "name": "кадастровый план земельного участка",
                "correct": false
            },
            {
                "id": "437969",
                "name": "технические условия",
                "correct": true
            },
            {
                "id": "437970",
                "name": "документация по планировке территории",
                "correct": false
            },
            {
                "id": "437971",
                "name": "разрешение на отклонение от предельных параметров разрешенного строительства, реконструкции объектов капитального строительства",
                "correct": true
            }
        ]
    },
    {
        "id": "437479",
        "name": "Какие сведения включаются в единый государственный реестр заключений экспертизы проектной документации объектов капитального строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437483",
                "name": "сведения о типовой проектной документации",
                "correct": true
            },
            {
                "id": "437482",
                "name": "сведения об экономически эффективной проектной документации повторного использования",
                "correct": false
            },
            {
                "id": "437485",
                "name": "сведения о документации по планировке территории, на основании которых была осуществлена подготовка проектной документации",
                "correct": false
            },
            {
                "id": "437484",
                "name": "сведения о представленных для проведения экспертизы результатах инженерных изысканий",
                "correct": true
            },
            {
                "id": "437481",
                "name": "сведения о представленной для проведения экспертизы проектной документации",
                "correct": true
            },
            {
                "id": "437480",
                "name": "сведения о заключениях экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "437671",
        "name": "Что понимается под определениями «декларирование соответствия» и «подтверждение соответствия»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437676",
                "name": "подтверждение соответствия - установление тождественности характеристик продукции её существующим признакам",
                "correct": false
            },
            {
                "id": "437677",
                "name": "подтверждение соответствия - документальное удостоверение соответствия продукции или иных объектов, процессов проектирования (включая изыскания), производства, строительства, монтажа, наладки, эксплуатации, хранения, перевозки, реализации и утилизации, выполнения работ или оказания услуг требованиям технических регламентов, документам по стандартизации или условиям договоров",
                "correct": true
            },
            {
                "id": "437672",
                "name": "декларирование соответствия - документ, удостоверяющий соответствие выпускаемой в обращение продукции требованиям технических регламентов",
                "correct": false
            },
            {
                "id": "437675",
                "name": "подтверждение соответствия - прямое или косвенное определение соблюдения требований, предъявляемых к объекту",
                "correct": false
            },
            {
                "id": "437674",
                "name": "декларирование соответствия - обозначение, служащее для информирования приобретателей, в том числе потребителей, о соответствии выпускаемой в обращение продукции требованиям технических регламентов",
                "correct": false
            },
            {
                "id": "437673",
                "name": "декларирование соответствия - форма подтверждения соответствия продукции требованиям технических регламентов",
                "correct": true
            }
        ]
    },
    {
        "id": "450676",
        "name": "Трансформаторные помещения и ЗРУ не допускается размещать, в соответствии с ПУЭ:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450680",
                "name": "под душевыми, ванными и т.п. помещениями, кроме трансформаторных помещений с сухими трансформаторами",
                "correct": false
            },
            {
                "id": "450679",
                "name": "под душевыми, ванными и т.п. помещениями",
                "correct": true
            },
            {
                "id": "450677",
                "name": "под помещениями производств с мокрым технологическим процессом",
                "correct": true
            },
            {
                "id": "450678",
                "name": "под помещениями производств с мокрым технологическим процессом, кроме РУ промышленных предприятий",
                "correct": false
            },
            {
                "id": "450682",
                "name": "непосредственно под помещениями, в которых в пределах площади, занимаемой РУ или трансформаторными помещениями может находиться более 50 человек в период более 1 часа, кроме трансформаторных помещений с сухими трансформаторами и РУ промышленных предприятий",
                "correct": true
            },
            {
                "id": "450681",
                "name": "непосредственно над помещениями, в которых в пределах площади, занимаемой РУ или трансформаторными помещениями может находиться более 50 человек в период более 1 часа, кроме трансформаторных помещений с сухими трансформаторами и РУ промышленных предприятий",
                "correct": true
            }
        ]
    },
    {
        "id": "450572",
        "name": "На какое время, в соответствии с ПУЭ, допускается отключить электроприемники I категории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450575",
                "name": "до 1 суток ",
                "correct": false
            },
            {
                "id": "450578",
                "name": "до устранения повреждения",
                "correct": false
            },
            {
                "id": "450576",
                "name": "до 10 суток",
                "correct": false
            },
            {
                "id": "450574",
                "name": "до 12-ти часов ",
                "correct": false
            },
            {
                "id": "450573",
                "name": "на время автоматического восстановления питания",
                "correct": true
            },
            {
                "id": "450577",
                "name": "на время выезда оперативной бригады",
                "correct": false
            }
        ]
    },
    {
        "id": "450458",
        "name": "Какая должна быть глубина заложения кабельных линий от планировочной отметки, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450461",
                "name": "при пересечении улиц и площадей независимо от напряжения - 1 м",
                "correct": true
            },
            {
                "id": "450460",
                "name": "линий 35 кВ не менее 1,0 м",
                "correct": true
            },
            {
                "id": "450459",
                "name": "линий до 20 кВ не менее 0,7 м",
                "correct": true
            },
            {
                "id": "450464",
                "name": "при пересечении улиц и площадей независимо от напряжения - 0,6 м",
                "correct": false
            },
            {
                "id": "450463",
                "name": "линий 35 кВ не менее 0,8 м",
                "correct": false
            },
            {
                "id": "450462",
                "name": "линий до 20 кВ не менее 0,5 м",
                "correct": false
            }
        ]
    },
    {
        "id": "450035",
        "name": "Какое наименьшее допустимое сечение кабелей и проводов с медными жилами в групповых сетях освещения в жилых зданиях?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450038",
                "name": "2,5 мм²",
                "correct": false
            },
            {
                "id": "450036",
                "name": "0,75 мм²",
                "correct": false
            },
            {
                "id": "450039",
                "name": "4 мм²",
                "correct": false
            },
            {
                "id": "450037",
                "name": "1,5 мм²",
                "correct": true
            }
        ]
    },
    {
        "id": "437604",
        "name": "По каким уровням ответственности идентифицируются здания и сооружения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437607",
                "name": "нормальный",
                "correct": true
            },
            {
                "id": "437608",
                "name": "опасный",
                "correct": false
            },
            {
                "id": "437610",
                "name": "особо опасный",
                "correct": false
            },
            {
                "id": "437609",
                "name": "пониженный",
                "correct": true
            },
            {
                "id": "437605",
                "name": "простой",
                "correct": false
            },
            {
                "id": "437606",
                "name": "повышенный",
                "correct": true
            }
        ]
    },
    {
        "id": "437403",
        "name": "Что из перечисленного входит в предмет государственной экспертизы проектной документации, подготовленной в целях неоднократного применения при архитектурно-строительном проектировании объектов капитального строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437407",
                "name": "оценка соответствия проектной документации требованиям к обеспечению надежности и безопасности электроэнергетических систем и объектов электроэнергетики",
                "correct": true
            },
            {
                "id": "437408",
                "name": "оценка соответствия проектной документации техническому заданию на подготовку проектной документации неоднократного применения",
                "correct": true
            },
            {
                "id": "437406",
                "name": "оценка соответствия проектной документации требованиям, установленным в разрешении на отклонение от предельных параметров разрешенного строительства",
                "correct": false
            },
            {
                "id": "437409",
                "name": "оценка соответствия проектной документации результатам инженерных изысканий",
                "correct": false
            },
            {
                "id": "437405",
                "name": "проверка достоверности определения сметной стоимости в случаях, установленных частью 2 статьи 8.3 Градостроительного кодекса Российской Федерации",
                "correct": false
            },
            {
                "id": "437404",
                "name": "оценка соответствия проектной документации требованиям в области охраны окружающей среды",
                "correct": true
            }
        ]
    },
    {
        "id": "450309",
        "name": "Какие меры должны быть предусмотрены при пересечении электропроводками строительных конструкций в жилых и общественных зданиях? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450312",
                "name": "в местах прохождения электропроводок через строительные конструкции с нормируемым пределом огнестойкости должны быть предусмотрены кабельные проходки с пределом огнестойкости не ниже предела огнестойкости строительных конструкций",
                "correct": true
            },
            {
                "id": "450311",
                "name": "проходы небронированных кабелей, защищенных и незащищенных проводов через несгораемые стены (перегородки) и междуэтажные перекрытия должны быть выполнены в отрезках труб, или в коробах, или проемах, а через сгораемые - в отрезках стальных труб без уплотнений",
                "correct": false
            },
            {
                "id": "450310",
                "name": "места прохода проводов в защитной оболочке и кабелей через стены, перегородки, междуэтажные перекрытия должны иметь уплотнения",
                "correct": false
            },
            {
                "id": "450314",
                "name": "проходы кабелей через стены, перегородки и перекрытия должны быть осуществлены через отрезки неметаллических труб (асбестовых безнапорных, пластмассовых и т.п.)",
                "correct": false
            },
            {
                "id": "450313",
                "name": "при пересечении строительных конструкций с ненормируемым пределом огнестойкости места прохода электропроводки должны быть заделаны строительным материалом группы горючести НГ",
                "correct": true
            }
        ]
    },
    {
        "id": "438091",
        "name": "Какие действия являются недопустимыми при проведении государственной экспертизы? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438095",
                "name": "уничтожение, исправление и изъятие документов, находящихся в деле экспертизы",
                "correct": true
            },
            {
                "id": "438097",
                "name": "досрочное расторжение договора на проведение государственной экспертизы по инициативе организации по проведению экспертизы",
                "correct": false
            },
            {
                "id": "438093",
                "name": "истребование от заявителей исходных данных, использованных для проектирования, в том числе материалов инженерных изысканий прошлых лет",
                "correct": false
            },
            {
                "id": "438094",
                "name": "выдача заключения до включения сведений о таком заключении в единый государственный реестр заключений экспертизы проектной документации объектов капитального строительства (за исключением случаев, если документы, необходимые для проведения государственной экспертизы, содержат сведения, составляющие государственную тайну)",
                "correct": true
            },
            {
                "id": "438092",
                "name": "истребование от заявителей сведений и документов, не предусмотренных Положением о порядке организации и проведении государственной экспертизы проектной документации и результатов инженерных изысканий, утвержденным постановлением Правительства Российской Федерации от 05.03.2007 № 145",
                "correct": true
            },
            {
                "id": "438096",
                "name": "изменение договорных условий, на основании которых оказывается услуга",
                "correct": false
            }
        ]
    },
    {
        "id": "450508",
        "name": "Какое расстояние допускается от взрывоопасных наружных установок до оси трассы ВЛ, в соответствии с ПУЭ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450510",
                "name": "не менее полуторакратной высоты провода над землей",
                "correct": false
            },
            {
                "id": "450513",
                "name": "не менее 150 м",
                "correct": false
            },
            {
                "id": "450511",
                "name": "не менее 10 м",
                "correct": false
            },
            {
                "id": "450512",
                "name": "не нормируется",
                "correct": false
            },
            {
                "id": "450509",
                "name": "не менее полуторакратной высоты опоры",
                "correct": true
            }
        ]
    },
    {
        "id": "437699",
        "name": "В каких формах осуществляется обязательное подтверждение соответствия?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437704",
                "name": "в форме стандартизации",
                "correct": false
            },
            {
                "id": "437703",
                "name": "в форме обязательной сертификации",
                "correct": true
            },
            {
                "id": "437705",
                "name": "в форме контроля (надзора) за соблюдением требований технических регламентов",
                "correct": false
            },
            {
                "id": "437700",
                "name": "в форме добровольной сертификации",
                "correct": false
            },
            {
                "id": "437702",
                "name": "за счёт внесения в единый перечень продукции, подлежащей обязательной сертификации",
                "correct": false
            },
            {
                "id": "437701",
                "name": "в форме принятия декларации о соответствии",
                "correct": true
            }
        ]
    },
    {
        "id": "450592",
        "name": "В качестве РЕ-проводников в электроустановках напряжением до 1 кВ, в соответствии с ПУЭ, могут использоваться:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450594",
                "name": "стационарно проложенные изолированные или неизолированные проводники",
                "correct": true
            },
            {
                "id": "450598",
                "name": "свинцовые оболочки кабелей  ",
                "correct": false
            },
            {
                "id": "450593",
                "name": "жилы многожильных кабелей",
                "correct": true
            },
            {
                "id": "450597",
                "name": "металлорукава",
                "correct": false
            },
            {
                "id": "450596",
                "name": "стальные трубы электропроводок",
                "correct": true
            },
            {
                "id": "450595",
                "name": "алюминиевые оболочки кабелей",
                "correct": true
            }
        ]
    },
    {
        "id": "437804",
        "name": "Кем и на какой срок устанавливается действие предоставленных технических условий подключения (технологического присоединения), выданные в целях заключения договора о подключении (технологическом присоединении)?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437808",
                "name": "срок действия технических условий устанавливается правообладателем сети инженерно-технического обеспечения не менее чем на пять лет при комплексном развитии территории, если иное не предусмотрено законодательством Российской Федерации",
                "correct": true
            },
            {
                "id": "437810",
                "name": "правообладателем земельного участка на срок не более, чем на пять лет",
                "correct": false
            },
            {
                "id": "437809",
                "name": "лицом, осуществляющим инженерные изыскания в составе инженерных изысканий не менее, чем на три года",
                "correct": false
            },
            {
                "id": "437806",
                "name": "федеральными органами исполнительной власти не менее, чем на три года по федеральным целевым программам",
                "correct": false
            },
            {
                "id": "437807",
                "name": "срок действия технических условий устанавливается правообладателем сети инженерно-технического обеспечения не менее чем на три года, если иное не предусмотрено законодательством Российской Федерации",
                "correct": true
            },
            {
                "id": "437805",
                "name": "местными органами исполнительной власти не менее, чем на три года",
                "correct": false
            }
        ]
    },
    {
        "id": "450662",
        "name": "Допускается ли в одном контрольном кабеле вторичных цепей электроустановок, в соответствии с ПУЭ, объединение следующих цепей:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450665",
                "name": "цепей управления, измерения, защиты и сигнализации постоянного тока",
                "correct": true
            },
            {
                "id": "450664",
                "name": "цепей управления, измерения, защиты и сигнализации переменного тока",
                "correct": true
            },
            {
                "id": "450667",
                "name": "цепей взаимно резервируемых устройств при наличии маркировки",
                "correct": false
            },
            {
                "id": "450668",
                "name": "объединение разных цепей в одном контрольном кабеле не допускается ",
                "correct": false
            },
            {
                "id": "450666",
                "name": "силовых цепей, питающих электроприёмники небольшой мощности (например, электродвигатели задвижек)",
                "correct": true
            },
            {
                "id": "450663",
                "name": "цепей управления, измерения, защиты и сигнализации постоянного и переменного тока",
                "correct": true
            }
        ]
    },
    {
        "id": "437678",
        "name": "Что понимается под определениями «декларация о соответствии» и «сертификат соответствия»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437680",
                "name": "сертификат соответствия - документ, подтверждающий соответствие объекта требованиям национальных стандартов и условиям договоров",
                "correct": false
            },
            {
                "id": "437684",
                "name": "сертификат соответствия - документ, служащий для информирования приобретателей о соответствии объекта сертификации требованиям системы добровольной сертификации или национальному стандарту",
                "correct": false
            },
            {
                "id": "437682",
                "name": "сертификат соответствия - документ, удостоверяющий соответствие объекта требованиям технических регламентов, документам по стандартизации или условиям договоров",
                "correct": true
            },
            {
                "id": "437683",
                "name": "декларация о соответствии - установление тождественности характеристик продукции её существующим признакам",
                "correct": false
            },
            {
                "id": "437681",
                "name": "декларация о соответствии - обозначение, служащее для информирования приобретателей, в том числе потребителей, о соответствии объекта сертификации требованиям системы добровольной сертификации",
                "correct": false
            },
            {
                "id": "437679",
                "name": "декларация о соответствии - документ, удостоверяющий соответствие выпускаемой в обращение продукции требованиям технических регламентов",
                "correct": true
            }
        ]
    },
    {
        "id": "450392",
        "name": "Какие электроприемники относятся к электроприемникам второй категории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450395",
                "name": "электроприемники, бесперебойная работа которых необходима для безаварийного останова производства в целях предотвращения угрозы жизни людей, взрывов и пожаров",
                "correct": false
            },
            {
                "id": "450394",
                "name": "электроприемники, перерыв электроснабжения которых приводит к массовому недоотпуску продукции, массовым простоям рабочих, механизмов и промышленного транспорта, нарушению нормальной деятельности значительного количества городских и сельских жителей",
                "correct": true
            },
            {
                "id": "450393",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой: опасность для жизни людей, угрозу для безопасности государства, значительный материальный ущерб, расстройство сложного технологического процесса, нарушение функционирования особо важных элементов коммунального хозяйства, объектов связи и телевидения",
                "correct": false
            }
        ]
    },
    {
        "id": "437948",
        "name": "В каком виде выполняется графическая часть проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437950",
                "name": "чертежи",
                "correct": true
            },
            {
                "id": "437952",
                "name": "планы",
                "correct": true
            },
            {
                "id": "437951",
                "name": "схемы",
                "correct": true
            },
            {
                "id": "437949",
                "name": "видеоматериалы",
                "correct": false
            }
        ]
    },
    {
        "id": "437590",
        "name": "По каким признакам идентифицируются здания и сооружения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437591",
                "name": "по назначению",
                "correct": true
            },
            {
                "id": "437596",
                "name": "по пожарной и взрывопожарной опасности",
                "correct": true
            },
            {
                "id": "437595",
                "name": "по конструктивным и объемно-планировочным решениям",
                "correct": false
            },
            {
                "id": "437594",
                "name": "по обеспечению доступа инвалидов",
                "correct": false
            },
            {
                "id": "437592",
                "name": "по принадлежности к опасным производственным объектам",
                "correct": true
            },
            {
                "id": "437593",
                "name": "по наличию помещений с постоянным пребыванием людей",
                "correct": true
            }
        ]
    },
    {
        "id": "437706",
        "name": "Какие функции выполняет орган по сертификации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437707",
                "name": "осуществляет подтверждение соответствия объектов добровольного подтверждения соответствия",
                "correct": true
            },
            {
                "id": "437708",
                "name": "ведет единый реестр зарегистрированных систем добровольной сертификации, содержащий сведения о юридических лицах и (или) об индивидуальных предпринимателях, создавших системы добровольной сертификации",
                "correct": false
            },
            {
                "id": "437711",
                "name": "утверждает документы по стандартизации",
                "correct": false
            },
            {
                "id": "437710",
                "name": "предоставляет заявителям право на применение знака соответствия, если применение знака соответствия предусмотрено соответствующей системой добровольной сертификации",
                "correct": true
            },
            {
                "id": "437712",
                "name": "приостанавливает или прекращает действие выданных им сертификатов соответствия",
                "correct": true
            },
            {
                "id": "437709",
                "name": "выдает сертификаты соответствия на объекты, прошедшие добровольную сертификацию",
                "correct": true
            }
        ]
    },
    {
        "id": "450405",
        "name": "Меры защиты от поражения электрическим током в случае повреждения изоляции при косвенном прикосновении:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450409",
                "name": "защитное заземление; автоматическое отключение питания; уравнивание потенциалов; выравнивание потенциалов; двойная или усиленная изоляция; сверхнизкое напряжение",
                "correct": false
            },
            {
                "id": "450407",
                "name": "защитное заземление; автоматическое отключение питания; уравнивание потенциалов; выравнивание потенциалов; двойная или усиленная изоляция; сверхнизкое (малое) напряжение; защитное электрическое разделение цепей; изолирующие (непроводящие) помещения, зоны, площадки",
                "correct": true
            },
            {
                "id": "450406",
                "name": "защитное заземление; основные средства защиты; выравнивание потенциалов; двойная или усиленная изоляция; сверхнизкое (малое) напряжение; защитное электрическое разделение цепей; изолирующие (непроводящие) помещения, зоны, площадки",
                "correct": false
            },
            {
                "id": "450408",
                "name": "защитное заземление; автоматическое отключение питания; дополнительные средства защиты; выравнивание потенциалов; двойная или усиленная изоляция; сверхнизкое (малое) напряжение; изолирующие (непроводящие) помещения, зоны, площадки",
                "correct": false
            }
        ]
    },
    {
        "id": "450017",
        "name": "Куда должны открываться двери электрощитовой или РУ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450023",
                "name": "в сторону большего по объему помещения",
                "correct": false
            },
            {
                "id": "450019",
                "name": "внутрь",
                "correct": false
            },
            {
                "id": "450018",
                "name": "наружу",
                "correct": true
            },
            {
                "id": "450021",
                "name": "в сторону других помещений",
                "correct": true
            },
            {
                "id": "450022",
                "name": "в обе стороны",
                "correct": false
            },
            {
                "id": "450020",
                "name": "не имеет значения",
                "correct": false
            }
        ]
    },
    {
        "id": "450655",
        "name": "В каких местах допускается не устанавливать аппараты защиты в соответствии с ПУЭ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450661",
                "name": "в местах ответвления от питающей линии проводников цепей измерения, управления и сигнализации, если эти проводники выходят за пределы соответствующих машин или щита, но электропроводка выполнена в трубах или имеет негорючую оболочку",
                "correct": true
            },
            {
                "id": "450658",
                "name": "в местах снижения сечения питающей линии по её длине и на ответвлениях от неё, если защита предыдущего участка линии защищает участок со сниженным сечением",
                "correct": true
            },
            {
                "id": "450656",
                "name": "в местах непосредственного присоединения защищаемых проводников к питающей линии при длине участка между питающей линией и аппаратом защиты ответвления до 6 м",
                "correct": true
            },
            {
                "id": "450657",
                "name": "в местах ответвления проводников от шин к аппаратам, установленным в том же щите, при этом проводники должны выбираться по току ответвления",
                "correct": true
            },
            {
                "id": "450659",
                "name": "в местах ответвления от питающей линии к электроприёмникам малой мощности, если питающая их линия защищена аппаратом с уставкой не более 25 А для силовых электроприёмников и бытовых приборов",
                "correct": true
            },
            {
                "id": "450660",
                "name": "в местах ответвления от питающей линии проводников цепей измерения, управления и сигнализации, если эти проводники не выходят за пределы соответствующих машин или щита",
                "correct": true
            }
        ]
    },
    {
        "id": "437382",
        "name": "При наличии каких оснований принимается решение об оставлении без рассмотрения документов, представленных для проведения государственной экспертизы?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437386",
                "name": "документы представлены с нарушением требований к формату документов, представляемых в электронной форме",
                "correct": true
            },
            {
                "id": "437388",
                "name": "отсутствие положительного заключения государственной экспертизы результатов инженерных изысканий (в случае, если проектная документация направлена на государственную экспертизу после государственной экспертизы результатов инженерных изысканий)",
                "correct": false
            },
            {
                "id": "437385",
                "name": "государственная экспертиза должна осуществляться иной организацией по проведению государственной экспертизы",
                "correct": false
            },
            {
                "id": "437384",
                "name": "документы предоставлены на бумажном носителе, в случае, когда проектная документация и (или) результаты инженерных изысканий не содержат сведения, составляющие государственную тайну",
                "correct": true
            },
            {
                "id": "437387",
                "name": "отсутствие в проектной документации разделов, которые подлежат включению в состав такой документации в соответствии с требованиями, предусмотренными законодательством о градостроительной деятельности",
                "correct": false
            },
            {
                "id": "437383",
                "name": "документы предоставлены на бумажном носителе, в случае, когда проектная документация и (или) результаты инженерных изысканий содержат сведения, составляющие государственную тайну",
                "correct": false
            }
        ]
    },
    {
        "id": "438019",
        "name": "Кто из перечисленных лиц может выполнять инженерные изыскания?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438024",
                "name": "юридическое лицо,  заключившее договор подряда  на выполнение инженерных изысканий и являющееся членом саморегулируемой организации в области инженерных изысканий\n\n\n\n",
                "correct": true
            },
            {
                "id": "438023",
                "name": "индивидуальный предприниматель, заключивший договор подряда на выполнение инженерных изысканий и являющийся членом саморегулируемой организации в области инженерных изысканий",
                "correct": true
            },
            {
                "id": "438021",
                "name": "юридическое лицо при наличии членства в саморегулируемой организации в области инженерных изысканий без заключения договора подряда на выполнение инженерных изысканий",
                "correct": false
            },
            {
                "id": "438022",
                "name": "физическое лицо, заключившее договор подряда на выполнение инженерных изысканий",
                "correct": false
            },
            {
                "id": "438020",
                "name": "застройщик, являющийся членом саморегулируемой организации в области инженерных изысканий",
                "correct": true
            }
        ]
    },
    {
        "id": "450356",
        "name": "На какие категории, как приемники электроэнергии, подразделяют зарядные станции и пункты зарядки для электромобилей, интегрированные в жилые и общественные здания?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450360",
                "name": "тип 3 - номинальный ток не превышает 63 А, номинальное напряжение 230 В при однофазном и 400 В при трехфазном подключении (стандартные зарядные станции)",
                "correct": false
            },
            {
                "id": "450362",
                "name": "тип 5 - номинальный ток превышает 125 А, номинальное напряжение 400 В при трехфазном подключении (мощные зарядные станции)",
                "correct": false
            },
            {
                "id": "450359",
                "name": "тип 3 - номинальный ток не превышает 63 А, номинальное напряжение 400 В при трехфазном подключении (стандартные зарядные станции)",
                "correct": true
            },
            {
                "id": "450361",
                "name": "тип 4 - номинальный ток превышает 63 А, номинальное напряжение 400 В при трехфазном подключении (быстрые зарядные станции)",
                "correct": true
            },
            {
                "id": "450357",
                "name": "тип 1 - номинальный ток не превышает 16 А, номинальное напряжение 230 В при однофазном и 400 В при трехфазном подключении (бытовые зарядные станции)",
                "correct": true
            },
            {
                "id": "450358",
                "name": "тип 2 - номинальный ток не превышает 32 А, номинальное напряжение 230 В при однофазном и 400 В при трехфазном подключении (медленные зарядные станции)",
                "correct": true
            }
        ]
    },
    {
        "id": "450669",
        "name": "Требования к проходам обслуживания распределительных устройств в электропомещениях, доступных только квалифицированному персоналу, в соответствии с ПУЭ:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450671",
                "name": "ширина прохода обслуживания, находящегося с лицевой или с задней стороны щита, должна быть не менее 1,2 м, в отдельных местах, ограниченных выступающими строительными конструкциями, не менее 0,8 м",
                "correct": false
            },
            {
                "id": "450670",
                "name": "ширина прохода обслуживания, находящегося с лицевой или с задней стороны щита, должна быть не менее 0,8 м, в отдельных местах, ограниченных выступающими строительными конструкциями, не менее 0,6 м",
                "correct": true
            },
            {
                "id": "450672",
                "name": "неограждённые неизолированные токоведущие части, размещённые над проходами, должны быть расположены на высоте не менее 2,2 м",
                "correct": true
            },
            {
                "id": "450675",
                "name": "проходы для обслуживания щитов при длине щита более 10 м и ширине прохода не более 3 м должны иметь два выхода",
                "correct": false
            },
            {
                "id": "450673",
                "name": "неограждённые неизолированные токоведущие части, размещённые над проходами, должны быть расположены на высоте не менее 1,9 м",
                "correct": false
            },
            {
                "id": "450674",
                "name": "проходы для обслуживания щитов при длине щита более 7 м и ширине прохода не более 3 м должны иметь два выхода",
                "correct": true
            }
        ]
    },
    {
        "id": "437941",
        "name": "Что содержит текстовая часть проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437946",
                "name": "спецификации материалов и оборудования",
                "correct": false
            },
            {
                "id": "437942",
                "name": "описание принятых технических и иных решений",
                "correct": true
            },
            {
                "id": "437944",
                "name": "ссылки на нормативные и (или) технические документы, используемые при подготовке проектной документации",
                "correct": true
            },
            {
                "id": "437945",
                "name": "результаты расчетов",
                "correct": true
            },
            {
                "id": "437947",
                "name": "выдержки из нормативных и (или) технических документы, обосновывающие принятые технические решения",
                "correct": false
            },
            {
                "id": "437943",
                "name": "расчетные обоснования",
                "correct": false
            }
        ]
    },
    {
        "id": "450613",
        "name": "Каким цветам, в соответствии с ПУЭ, должна соответствовать электропроводка в целях лёгкого распознавания проводников?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450618",
                "name": "двухцветной комбинации зелёно-жёлтого цвета по всей длине с голубыми метками на концах линии, которые наносятся при монтаже – для обозначения совмещённого нулевого рабочего или нулевого защитного проводника",
                "correct": true
            },
            {
                "id": "450617",
                "name": "чёрного цвета - для обозначения нулевого рабочего или среднего проводника электрической сети",
                "correct": false
            },
            {
                "id": "450616",
                "name": "двухцветной комбинации зелёно-жёлтого цвета – для обозначения защитного или нулевого защитного проводника",
                "correct": true
            },
            {
                "id": "450615",
                "name": "голубого цвета - для обозначения нулевого рабочего или среднего проводника электрической сети ",
                "correct": true
            },
            {
                "id": "450619",
                "name": "коричневого, красного, фиолетового, серого, розового, белого, оранжевого, бирюзового цвета – для обозначения фазного проводника",
                "correct": true
            },
            {
                "id": "450614",
                "name": "допускается выбирать любые цвета изоляции проводников при наличии бирок с обозначениями",
                "correct": false
            }
        ]
    },
    {
        "id": "450683",
        "name": "На каком минимальном расстоянии по условиям пожарной безопасности комплектные, столбовые, мачтовые трансформаторные подстанции с массой масла в единице оборудования менее 60 кг должны располагаться от зданий и сооружений в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450688",
                "name": "на расстоянии не менее 10 м от жилых зданий при условии обеспечения допустимых нормальных уровней шума",
                "correct": true
            },
            {
                "id": "450687",
                "name": "на расстоянии не менее 10 м от зданий IV, V степеней огнестойкости",
                "correct": false
            },
            {
                "id": "450686",
                "name": "на расстоянии не менее 5 м от зданий IV, V степеней огнестойкости",
                "correct": true
            },
            {
                "id": "450689",
                "name": "на расстоянии не менее 15 м от жилых зданий при условии обеспечения допустимых нормальных уровней шума",
                "correct": false
            },
            {
                "id": "450684",
                "name": "на расстоянии не менее 3 м от зданий I, II, III степеней огнестойкости",
                "correct": true
            },
            {
                "id": "450685",
                "name": "на расстоянии не менее 5 м от зданий I, II, III степеней огнестойкости",
                "correct": false
            }
        ]
    },
    {
        "id": "450370",
        "name": "Какое расстояние от наружных взрывоопасных установок при наличии тяжелого газа до закрытых распределительных устройств является допустимым?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450375",
                "name": "75 м",
                "correct": true
            },
            {
                "id": "450372",
                "name": "25 м",
                "correct": false
            },
            {
                "id": "450376",
                "name": "10 м",
                "correct": false
            },
            {
                "id": "450374",
                "name": "80 м",
                "correct": true
            },
            {
                "id": "450371",
                "name": "60 м",
                "correct": true
            },
            {
                "id": "450373",
                "name": "30 м",
                "correct": false
            }
        ]
    },
    {
        "id": "450102",
        "name": "Какое значение сопротивления заземлителя, расположенного в непосредственной близости от нейтрали стационарного генератора или трансформатора должно быть при линейном напряжении 380 В источника трехфазного тока?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450103",
                "name": "не более 60 Ом",
                "correct": false
            },
            {
                "id": "450107",
                "name": "не более 100 Ом",
                "correct": false
            },
            {
                "id": "450104",
                "name": "не более 50 Ом ",
                "correct": false
            },
            {
                "id": "450106",
                "name": "не более 30 Ом",
                "correct": true
            },
            {
                "id": "450105",
                "name": "не более 40 Ом",
                "correct": false
            }
        ]
    },
    {
        "id": "450387",
        "name": "Характеристика помещения с повышенной опасностью в отношении поражения людей электрическим током?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450389",
                "name": "наличием одного из следующих условий: особая сырость или пожароопасная пыль; токопроводящие полы; высокая температура; возможность одновременного прикосновения человека к металлоконструкциям зданий, имеющим соединение с землей, с одной стороны, и к металлическим корпусам электрооборудования, с другой",
                "correct": false
            },
            {
                "id": "450388",
                "name": "наличием одного из следующих условий: сырость или токопроводящая пыль; токопроводящие полы; высокая температура; химически активная или органическая среда; возможность одновременного прикосновения человека к металлоконструкциям зданий, имеющим соединение с землей, с одной стороны, и к металлическим корпусам электрооборудования, с другой",
                "correct": false
            },
            {
                "id": "450390",
                "name": "наличием одного из следующих условий: токопроводящая пыль или полы; высокая температура; возможность одновременного прикосновения человека к металлоконструкциям зданий, имеющим соединение с землей, с одной стороны, и к металлическим корпусам электрооборудования, с другой",
                "correct": false
            },
            {
                "id": "450391",
                "name": "наличием одного из следующих условий: сырость или токопроводящая пыль; токопроводящие полы; высокая температура; возможность одновременного прикосновения человека к металлоконструкциям зданий, имеющим соединение с землей, технологическим аппаратам, механизмам и т.п., с одной стороны, и к металлическим корпусам электрооборудования (открытым проводящим частям), с другой",
                "correct": true
            }
        ]
    },
    {
        "id": "437445",
        "name": "К каким сведениям единого государственного реестра заключений экспертизы проектной документации объектов капитального строительства обеспечивается доступ всем заинтересованным лицам на бесплатной основе в сети \"Интернет\" в форме открытых данных?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437447",
                "name": "сведения о застройщике (техническом заказчике), обеспечившем подготовку проектной документации, по результатам рассмотрения которой подготовлено заключение экспертизы ",
                "correct": true
            },
            {
                "id": "437446",
                "name": "форма экспертизы (государственная, негосударственная)",
                "correct": true
            },
            {
                "id": "437450",
                "name": " результат проведенной экспертизы (положительное или отрицательное заключение экспертизы)",
                "correct": true
            },
            {
                "id": "437451",
                "name": "сведения о проектной документации, составляющие государственную тайну",
                "correct": false
            },
            {
                "id": "437449",
                "name": "сведения об экспертной организации",
                "correct": true
            },
            {
                "id": "437448",
                "name": "сведения о лице, уполномоченном на распоряжение исключительным правом на типовую проектную документацию",
                "correct": false
            }
        ]
    },
    {
        "id": "438058",
        "name": "Каковы последствия непредставления правоустанавливающих документов на земельный участок при направлении проектной документации на экспертизу? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438060",
                "name": "основание для принятия решения об отказе от рассмотрения документации, представленной для проведения экспертизы",
                "correct": false
            },
            {
                "id": "438061",
                "name": "не влияет на оказание услуги по проведению экспертизы проектной документации",
                "correct": true
            },
            {
                "id": "438062",
                "name": "основание для направления замечания к представленной проектной документации",
                "correct": false
            },
            {
                "id": "438059",
                "name": "основание для принятия решения об отказе в приеме проектной документации на экспертизу",
                "correct": false
            },
            {
                "id": "438064",
                "name": "не является основанием для отказа в приеме проектной документации на экспертизу",
                "correct": true
            },
            {
                "id": "438063",
                "name": "основание для досрочного расторжения договора о проведении экспертизы в соответствии с пунктом 35 Положения о порядке организации и проведении государственной экспертизы проектной документации и результатов инженерных изысканий, утвержденного постановлением Правительства Российской Федерации от 05.03.2007 № 145",
                "correct": false
            }
        ]
    },
    {
        "id": "450144",
        "name": "Какие электроприемники жилых и общественных зданий относятся к электроприемникам II категории по надежности электроснабжения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450147",
                "name": "комплекс основных электроприемников зданий учреждений управления, проектных и конструкторских организаций, научно-исследовательских институтов с числом работающих свыше 50 человек, а также здания областного, городского и районного значения до 50 человек",
                "correct": true
            },
            {
                "id": "450150",
                "name": "зарядные станции и пункты зарядки для электромобилей",
                "correct": false
            },
            {
                "id": "450146",
                "name": "электроприемники операционных и родильных блоков, отделений анестезиологии, реанимации и интенсивной терапии, кабинетов лапароскопии, бронхоскопии и ангиографии, противопожарных устройств и охранной сигнализации, эвакуационного освещения и больничных лифтов",
                "correct": false
            },
            {
                "id": "450145",
                "name": "отдельно стоящие и встроенные центральные тепловые пункты (ЦТП), индивидуальные тепловые пункты (ИТП) многоквартирных жилых домов",
                "correct": false
            },
            {
                "id": "450148",
                "name": "здания учреждений управления, проектных и конструкторских организаций, научно-исследовательских институтов с числом работающих до 50 человек",
                "correct": false
            },
            {
                "id": "450149",
                "name": "комплекс основных электроприемников салонов-парикмахерских с числом рабочих мест свыше 15, ателье и комбинатов бытового обслуживания с числом рабочих мест свыше 50, прачечных и химчисток производительностью свыше 500 кг белья в смену, бань с числом мест свыше 100",
                "correct": true
            }
        ]
    },
    {
        "id": "450436",
        "name": "Укажите наименьшее сечение медных защитных проводников, не входящих в состав кабеля или проложенных не в общей оболочке (трубе, коробе, на одном лотке) с фазными проводниками, при отсутствии механической защиты, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450437",
                "name": "2,5 мм²",
                "correct": false
            },
            {
                "id": "450439",
                "name": "6 мм²",
                "correct": false
            },
            {
                "id": "450440",
                "name": "16 мм²",
                "correct": false
            },
            {
                "id": "450438",
                "name": "4 мм²",
                "correct": true
            }
        ]
    },
    {
        "id": "450060",
        "name": "Каким сечением должны быть медные проводники для питания однофазных электроплит в квартирах жилых домов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450063",
                "name": "не менее 6 мм²",
                "correct": true
            },
            {
                "id": "450064",
                "name": "не менее 10 мм²",
                "correct": false
            },
            {
                "id": "450061",
                "name": "не менее 2,5 мм²",
                "correct": false
            },
            {
                "id": "450062",
                "name": "не менее 4,0 мм²",
                "correct": false
            }
        ]
    },
    {
        "id": "450295",
        "name": "Какие специальные меры повышения надежности могут быть предусмотрены в наиболее ответственных осветительных установках для питания аварийного эвакуационного освещения? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450296",
                "name": "применение аккумуляторных батарей (продолжительность работы светильников аварийного эвакуационного освещения при питании их от аккумуляторов должна быть не менее 3 часов)",
                "correct": false
            },
            {
                "id": "450299",
                "name": "применение дополнительного устройства АВР (автоматический ввод резерва), который следует подключать до вводных аппаратов защиты вводно-распределительных устройств, главных распределительных щитов",
                "correct": true
            },
            {
                "id": "450298",
                "name": "применение двух отдельных взаимно резервирующих вводов от трансформаторной подстанции для питания эвакуационного освещения и других систем безопасности, работоспособность которых должна обеспечиваться в условиях пожара",
                "correct": true
            },
            {
                "id": "450297",
                "name": "применение двух независимых источников питания: независимого ввода электроснабжения и источника резервного питания - аккумуляторные батареи или генераторную установку (особая группа электроприемников I категории)",
                "correct": false
            },
            {
                "id": "450300",
                "name": "к аппаратам защиты в распределительном устройстве низкого напряжения трансформаторной подстанции и к линиям питания могут быть подключены иные нагрузки, кроме эвакуационного освещения (включая знаки безопасности) и других систем безопасности зданий, работоспособность которых должна обеспечиваться в условиях пожара",
                "correct": false
            },
            {
                "id": "450301",
                "name": "применение трех независимых источников питания: двух независимых вводов электроснабжения и источник резервного питания - аккумуляторные батареи или генераторную установку (особая группа электроприемников I категории)",
                "correct": true
            }
        ]
    },
    {
        "id": "450410",
        "name": "При каких уровнях напряжения следует выполнять защиту при косвенном прикосновении во всех случаях?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450411",
                "name": "защиту при косвенном прикосновении следует выполнять во всех случаях, если напряжение в электроустановке превышает 50 В переменного тока и 120 В постоянного тока",
                "correct": true
            },
            {
                "id": "450414",
                "name": "во всех случаях, если напряжение в электроустановке превышает 127 В переменного и 400 В постоянного тока",
                "correct": false
            },
            {
                "id": "450412",
                "name": "во всех случаях, если напряжение в электроустановке превышает 12 В переменного и 60 В постоянного тока",
                "correct": false
            },
            {
                "id": "450413",
                "name": "во всех случаях, если напряжение в электроустановке превышает 24 В переменного и 90 В постоянного тока",
                "correct": false
            }
        ]
    },
    {
        "id": "437783",
        "name": "Кто может являться лицом, осуществляющим подготовку проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437788",
                "name": "индивидуальный предприниматель, заключивший договор подряда на подготовку проектной документации",
                "correct": true
            },
            {
                "id": "437787",
                "name": "физическое лицо, аттестованное на право подготовки заключений экспертизы проектной документации и результатов инженерных изысканий ",
                "correct": false
            },
            {
                "id": "437789",
                "name": "юридическое лицо, заключившие договор подряда на подготовку проектной документации",
                "correct": true
            },
            {
                "id": "437785",
                "name": "технический заказчик (без заключения договора подряда на подготовку проектной документации, при условии, что он не является членом саморегулируемой организации в области архитектурно-строительного проектирования)",
                "correct": false
            },
            {
                "id": "437786",
                "name": "региональный оператор (без заключения договора подряда на подготовку проектной документации, при условии, что он не является членом саморегулируемой организации в области архитектурно-строительного проектирования)",
                "correct": false
            },
            {
                "id": "437784",
                "name": "застройщик (при условии, что он является членом саморегулируемой организации в области архитектурно-строительного проектирования)",
                "correct": true
            }
        ]
    },
    {
        "id": "437354",
        "name": "Что такое «этап строительства»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437360",
                "name": "комплекс работ по подготовке территории строительства, включающий вырубку леса при строительстве",
                "correct": true
            },
            {
                "id": "437356",
                "name": "строительство части объекта капитального строительства, которая может быть введена в эксплуатацию и эксплуатироваться автономно",
                "correct": true
            },
            {
                "id": "437359",
                "name": "комплекс работ по подготовке территории строительства, включающий переустройство (перенос) инженерных коммуникаций",
                "correct": true
            },
            {
                "id": "437357",
                "name": "строительство или реконструкция объекта капитального строительства из числа объектов капитального строительства, планируемых к реконструкции на одном земельном участке, если такой объект может быть введен в эксплуатацию и эксплуатироваться автономно",
                "correct": true
            },
            {
                "id": "437358",
                "name": "реконструкция части объекта капитального строительства, которая может быть введена в эксплуатацию и эксплуатироваться автономно",
                "correct": true
            },
            {
                "id": "437355",
                "name": "строительство объекта капитального строительства из числа объектов капитального строительства, планируемых к строительству, если такой объект может быть введен в эксплуатацию и эксплуатироваться автономно",
                "correct": false
            }
        ]
    },
    {
        "id": "437348",
        "name": "Допускается ли предоставлять проектную документацию и (или) результаты инженерных изысканий в организации, уполномоченные на проведение экспертизы, на бумажном носителе (не в электронной форме)?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437353",
                "name": "допускается, в случае предоставление проектной документации и (или) результатов инженерных изысканий в организации негосударственной экспертизы",
                "correct": false
            },
            {
                "id": "437350",
                "name": "допускается, в случае, когда проектная документация и (или) результаты инженерных изысканий содержат сведения, составляющие государственную тайну",
                "correct": true
            },
            {
                "id": "437349",
                "name": "допускается, в случаях, предусмотренных законодательством Российской Федерации",
                "correct": true
            },
            {
                "id": "437351",
                "name": "не допускается",
                "correct": false
            },
            {
                "id": "437352",
                "name": "допускается в случае представления проектной документации и (или) результатов инженерных изысканий в форме информационной модели",
                "correct": false
            }
        ]
    },
    {
        "id": "450070",
        "name": "Необходимо ли устанавливать светильник над каждым входом в жилое здание?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450073",
                "name": "не нужно, если есть уличное освещение",
                "correct": false
            },
            {
                "id": "450071",
                "name": "необходимо ",
                "correct": true
            },
            {
                "id": "450074",
                "name": "не нужно при наличии световых указателей",
                "correct": false
            },
            {
                "id": "450072",
                "name": "не обязательно ",
                "correct": false
            }
        ]
    },
    {
        "id": "450332",
        "name": "Какие состояния ВЛ до 1 кВ учитываются при расчетах механической части:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450335",
                "name": "аварийный режим - режим при оборванных проводах; монтажный режим - режим в условиях монтажа опор и проводов; механический расчет ВЛ до 1 кВ в аварийном режиме не производится",
                "correct": false
            },
            {
                "id": "450334",
                "name": "нормальный режим - режим при необорванных проводах; аварийный режим - режим при оборванных проводах; механический расчет ВЛ до 1 кВ в аварийном режиме не производится",
                "correct": false
            },
            {
                "id": "450336",
                "name": "нормальный режим - режим при необорванных проводах; аварийный режим - режим при оборванных проводах; монтажный режим- режим в условиях монтажа опор и проводов; расчет производится для всех режимов",
                "correct": false
            },
            {
                "id": "450333",
                "name": "нормальный режим – режим при необорванных проводах; аварийный режим – режим при оборванных проводах; монтажный режим – режим в условиях монтажа опор и проводов; механический расчет ВЛ до 1 кВ в аварийном режиме не производится",
                "correct": true
            }
        ]
    },
    {
        "id": "437465",
        "name": "Каким требованиям, из перечисленных, должны соответствовать электронные документы, предоставляемые на экспертизу в форматах doc, docks, odt, pdf?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437469",
                "name": "электронные документы, представляемые в форматах doc, docx, odt, pdf должны содержать оглавление (для документов, содержащих структурированные по частям, главам, разделам (подразделам) данные)",
                "correct": true
            },
            {
                "id": "437467",
                "name": "электронные документы, представляемые в форматах doc, docx, odt, pdf должны состоять из одного или нескольких файлов, каждый из которых содержит текстовую и (или) графическую информацию",
                "correct": true
            },
            {
                "id": "437466",
                "name": "электронные документы, представляемые в форматах doc, docx, odt, pdf должны формироваться способом, не предусматривающим сканирование документа на бумажном носителе",
                "correct": true
            },
            {
                "id": "437470",
                "name": "электронные документы, представляемые в форматах doc, docx, odt, pdf должны содержать закладки, обеспечивающие переходы по оглавлению и (или) к содержащимся в тексте рисункам и таблицам",
                "correct": true
            },
            {
                "id": "437471",
                "name": "электронные документы, представляемые в форматах doc, docx, odt, pdf должны не превышать предельного размера в 50 мегабайт (в случае превышения предельного размера, документ делится на несколько, название каждого файла дополняется словом \"Фрагмент\" и порядковым номером файла, полученного в результате деления)",
                "correct": false
            },
            {
                "id": "437468",
                "name": "электронные документы, представляемые в форматах doc, docx, odt, pdf должны обеспечивать возможность поиска по текстовому содержанию документа и возможность копирования текста (за исключением случаев, когда текст является частью графического изображения)",
                "correct": true
            }
        ]
    },
    {
        "id": "438078",
        "name": "Какие требования установлены к оперативному внесению изменений в проектную документацию и (или) результаты инженерных изысканий? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438083",
                "name": "при подготовке заключения по результатам оценки соответствия в рамках экспертного сопровождения изменений, внесённых в проектную документацию после выдачи положительного заключения экспертизы, может осуществляться оперативное внесение изменений в разделы проектной документации",
                "correct": false
            },
            {
                "id": "438079",
                "name": "осуществляется в целях устранения несоответствий, выявленных экспертами в ходе проведения экспертизы",
                "correct": true
            },
            {
                "id": "438080",
                "name": "при проведении государственной экспертизы проектной документации и результатов инженерных изысканий может осуществляться оперативное внесение изменений, но не позднее чем за 5 рабочих дней до окончания срока проведения государственной экспертизы",
                "correct": false
            },
            {
                "id": "438081",
                "name": "при подготовке заключения государственной экспертизы по результатам экспертного сопровождения может осуществляться оперативное внесение изменений в смету на строительство, реконструкцию",
                "correct": true
            },
            {
                "id": "438082",
                "name": "при подготовке заключения государственной экспертизы по результатам экспертного сопровождения может осуществляться оперативное внесение изменений в любые разделы проектной документации",
                "correct": true
            }
        ]
    },
    {
        "id": "450537",
        "name": "Какой может быть угол пересечения воздушных линий электропередачи напряжением выше 1 кВ с автомобильными дорогами? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450538",
                "name": "угол пресечения с улицами (проездами) не нормируется",
                "correct": true
            },
            {
                "id": "450540",
                "name": "угол не более 90° при пресечении с улицами (проездами)",
                "correct": false
            },
            {
                "id": "450543",
                "name": "угол близкий к 60° при пресечении с улицами (проездами)",
                "correct": false
            },
            {
                "id": "450542",
                "name": "угол близкий к 90° при пресечении с улицами (проездами)",
                "correct": false
            },
            {
                "id": "450539",
                "name": "угол 90° при пресечении с улицами (проездами)",
                "correct": false
            },
            {
                "id": "450541",
                "name": "угол не менее 90° при пресечении с улицами (проездами)",
                "correct": false
            }
        ]
    },
    {
        "id": "450400",
        "name": "Что понимается под изолированной нейтралью электрической сети?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450404",
                "name": "трехфазная электрическая сеть напряжением выше 1 кВ, в которой коэффициент замыкания на землю не превышает 1,4",
                "correct": false
            },
            {
                "id": "450403",
                "name": "нейтраль трансформатора или генератора, присоединенная непосредственно к заземляющему устройству или также вывод источника однофазного переменного тока или полюс источника постоянного тока в двухпроводных сетях, а также средняя точка в трехпроводных сетях постоянного тока",
                "correct": false
            },
            {
                "id": "450402",
                "name": "нейтраль трансформатора или генератора, присоединенная к заземляющему устройству через большое сопротивление приборов сигнализации, измерения, защиты и других аналогичных им устройств",
                "correct": true
            },
            {
                "id": "450401",
                "name": "нейтраль трансформатора или генератора, не присоединенная к заземляющему устройству",
                "correct": true
            }
        ]
    },
    {
        "id": "450089",
        "name": "На какие виды подразделяется аварийное освещение в соответствии с СП 52.13330.2016?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450091",
                "name": "освещение безопасности",
                "correct": false
            },
            {
                "id": "450092",
                "name": "эвакуационное освещение",
                "correct": true
            },
            {
                "id": "450094",
                "name": "архитектурное освещение",
                "correct": false
            },
            {
                "id": "450090",
                "name": "резервное освещение",
                "correct": true
            },
            {
                "id": "450093",
                "name": "дежурное освещение",
                "correct": false
            }
        ]
    },
    {
        "id": "450315",
        "name": "Где следует предусматривать освещение путей эвакуации в помещениях, местах производства работ вне зданий и на технологических площадках промышленных предприятий? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450316",
                "name": "в коридорах и проходах по путям эвакуации",
                "correct": true
            },
            {
                "id": "450318",
                "name": "в зоне каждого изменения направления пути",
                "correct": true
            },
            {
                "id": "450321",
                "name": "во всех примыкающих к пути эвакуации коридорах ",
                "correct": false
            },
            {
                "id": "450320",
                "name": "снаружи перед конечным выходом из здания или сооружения",
                "correct": true
            },
            {
                "id": "450317",
                "name": "в местах изменения (перепада) уровня пола или покрытия",
                "correct": true
            },
            {
                "id": "450319",
                "name": "перед пунктом медицинской помощи",
                "correct": true
            }
        ]
    },
    {
        "id": "450502",
        "name": "Не менее какой, в соответствии с ПУЭ, должна быть длительно допустимая токовая нагрузка проводников ответвлений к короткозамкнутым электродвигателям? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450504",
                "name": "100% номинального тока электродвигателя в невзрывоопасных зонах",
                "correct": true
            },
            {
                "id": "450505",
                "name": "125% номинального тока электродвигателя во взрывоопасных зонах",
                "correct": true
            },
            {
                "id": "450506",
                "name": "110% номинального тока электродвигателя в невзрывоопасных зонах",
                "correct": false
            },
            {
                "id": "450507",
                "name": "130% номинального тока электродвигателя во взрывоопасных зонах",
                "correct": false
            },
            {
                "id": "450503",
                "name": "110% номинального тока электродвигателя в нормальных зонах",
                "correct": false
            }
        ]
    },
    {
        "id": "437562",
        "name": "Если для конкретного раздела проектной документации отсутствует утвержденная xml-схема формирования документа, в каком формате допускается представлять на экспертизу текстовый документ, не содержащий формул?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437567",
                "name": "JPG",
                "correct": false
            },
            {
                "id": "437566",
                "name": "ODT",
                "correct": true
            },
            {
                "id": "437564",
                "name": "DOCX",
                "correct": true
            },
            {
                "id": "437568",
                "name": "TXT",
                "correct": false
            },
            {
                "id": "437563",
                "name": "в любом формате, позволяющим включать в документ текстовое содержание",
                "correct": false
            },
            {
                "id": "437565",
                "name": "DOC",
                "correct": true
            }
        ]
    },
    {
        "id": "449969",
        "name": "Прохождение ВЛ до 1 кВ с изолированными проводами не допускается по территориям?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "449973",
                "name": "детских оздоровительных лагерей",
                "correct": true
            },
            {
                "id": "449975",
                "name": "канатных дорог",
                "correct": false
            },
            {
                "id": "449970",
                "name": "спортивных сооружений",
                "correct": true
            },
            {
                "id": "449972",
                "name": "детских игровых площадок",
                "correct": true
            },
            {
                "id": "449971",
                "name": "производственных предприятий",
                "correct": false
            },
            {
                "id": "449974",
                "name": "образовательных учреждений любого типа",
                "correct": false
            }
        ]
    },
    {
        "id": "450702",
        "name": "Применение каких проводов и кабелей, в соответствии с ПУЭ, разрешается во взрывоопасных зонах В-I и В-Iа?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450704",
                "name": "проводов с резиновой и ПВХ изоляцией ",
                "correct": true
            },
            {
                "id": "450707",
                "name": "кабелей с алюминиевой оболочкой при прокладке в кабельных каналах",
                "correct": false
            },
            {
                "id": "450706",
                "name": "кабелей с алюминиевой оболочкой при открытой прокладке",
                "correct": false
            },
            {
                "id": "450708",
                "name": "проводов и кабелей с полиэтиленовой изоляцией или оболочкой",
                "correct": false
            },
            {
                "id": "450703",
                "name": "прокладка любых проводов и кабелей запрещается",
                "correct": false
            },
            {
                "id": "450705",
                "name": "кабелей с резиновой, ПВХ и бумажной изоляцией в резиновой, ПВХ и металлической оболочке",
                "correct": true
            }
        ]
    },
    {
        "id": "450302",
        "name": "Какие заземлители допустимы для прокладки в земле в соответствии с ГОСТ Р 50571.5.54-2024?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450305",
                "name": "круглые стержни для электродов, устанавливаемых вертикально, из стали горячего цинкования или нержавеющей диаметром 16 мм",
                "correct": true
            },
            {
                "id": "450303",
                "name": "сталь трубная из стали горячего цинкования или нержавеющей с площадью поперечного диаметром 25 мм",
                "correct": true
            },
            {
                "id": "450306",
                "name": "круглые стержни для электродов, устанавливаемых вертикально, из стали чёрной диаметром не менее 16 мм",
                "correct": false
            },
            {
                "id": "450304",
                "name": "круглые стержни для электродов, устанавливаемых вертикально, из стали горячего цинкования или нержавеющей диаметром не менее 12 мм",
                "correct": false
            },
            {
                "id": "450308",
                "name": "полоса стальная горячего цинкования или нержавеющая с площадью поперечного сечения 90 мм²",
                "correct": true
            },
            {
                "id": "450307",
                "name": "круглая проволока для электродов, устанавливаемых горизонтально, из стали горячего цинкования или нержавеющей диаметром 10 мм",
                "correct": true
            }
        ]
    },
    {
        "id": "437993",
        "name": "Физические лица, аттестованные на право подготовки заключений экспертизы проектной документации и (или) результатов инженерных изысканий в соответствии со статьей 49.1 Градостроительного кодекса Российской Федерации, не вправе участвовать в проведении экспертизы проектной документации при наличии личной заинтересованности в результатах такой экспертизы, в том числе: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437998",
                "name": "если в подготовке проектной документации и (или) выполнении инженерных изысканий участвовал их непосредственный руководитель",
                "correct": false
            },
            {
                "id": "437994",
                "name": "если в подготовке проектной документации и (или) выполнении инженерных изысканий участвовали указанные лица лично ",
                "correct": true
            },
            {
                "id": "437995",
                "name": "если в подготовке проектной документации и (или) выполнении инженерных изысканий участвовали их близкие родственники",
                "correct": true
            },
            {
                "id": "437997",
                "name": "если в подготовке проектной документации и (или) выполнении инженерных изысканий участвовали их друзья или родственники не являющиеся близкими",
                "correct": false
            },
            {
                "id": "437996",
                "name": "если в подготовке проектной документации и (или) выполнении инженерных изысканий участвовали их супруг/супруга",
                "correct": true
            }
        ]
    },
    {
        "id": "450606",
        "name": "Допускается ли объединение функций нулевого защитного проводника РЕ и нулевого рабочего проводника N в одном общем проводнике PEN внутри передвижной электроустановки, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450607",
                "name": "допускается во всех случаях",
                "correct": false
            },
            {
                "id": "450612",
                "name": "не допускается во всех случаях",
                "correct": true
            },
            {
                "id": "450611",
                "name": "не допускается при питании передвижной электроустановки от автономных передвижных источников электроэнергии",
                "correct": false
            },
            {
                "id": "450608",
                "name": "допускается при питании передвижной электроустановки от стационарной электрической сети",
                "correct": false
            },
            {
                "id": "450610",
                "name": "не допускается при питании передвижной электроустановки от стационарной электрической сети",
                "correct": false
            },
            {
                "id": "450609",
                "name": "допускается при питании передвижной электроустановки от автономных передвижных источников электроэнергии",
                "correct": false
            }
        ]
    },
    {
        "id": "450045",
        "name": "При установке УДТ последовательно должно выполняться требование селективности. Во сколько раз номинальный отключающий дифференциальный ток УЗО, расположенного ближе к источнику питания, должен быть больше, чем у УДТ, расположенного ближе к потребителю?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450049",
                "name": "не менее чем в 10 раз",
                "correct": false
            },
            {
                "id": "450046",
                "name": "не менее чем в три раза ",
                "correct": true
            },
            {
                "id": "450048",
                "name": "в два раза",
                "correct": false
            },
            {
                "id": "450047",
                "name": "может быть одинаковым ",
                "correct": false
            }
        ]
    },
    {
        "id": "450322",
        "name": "Защита воздушных и кабельных линий в сетях напряжением 3-10 кВ с изолированной нейтралью:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450324",
                "name": "для линий в сетях 3-10 кВ с изолированной должны быть предусмотрены устройства релейной защиты от однофазных замыканий на землю",
                "correct": false
            },
            {
                "id": "450326",
                "name": "для линий в сетях 10 кВ с изолированной нейтралью должны быть предусмотрены устройства релейной защиты от многофазных замыканий на землю ",
                "correct": false
            },
            {
                "id": "450323",
                "name": "для линий в сетях 3-10 кВ с изолированной нейтралью должны быть предусмотрены устройства релейной защиты от многофазных замыканий и от однофазных замыканий на землю",
                "correct": true
            },
            {
                "id": "450325",
                "name": "для линий в сетях 10 кВ с изолированной должны быть предусмотрены устройства релейной защиты от многофазных замыканий и от однофазных замыканий на землю",
                "correct": true
            }
        ]
    },
    {
        "id": "437880",
        "name": "В каких случаях подготовка проектной документации может осуществляться применительно к отдельным этапам строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437884",
                "name": "при перепланировке",
                "correct": false
            },
            {
                "id": "437885",
                "name": "при переустройстве",
                "correct": false
            },
            {
                "id": "437881",
                "name": "при строительстве",
                "correct": true
            },
            {
                "id": "437882",
                "name": "при реконструкции",
                "correct": true
            },
            {
                "id": "437886",
                "name": "при выводе из эксплуатации",
                "correct": false
            },
            {
                "id": "437883",
                "name": "при капитальном ремонте",
                "correct": false
            }
        ]
    },
    {
        "id": "450024",
        "name": "Какая температура должна быть в помещении электрощитовых в жилых и общественных зданиях?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450029",
                "name": "не ниже плюс 20 °С",
                "correct": false
            },
            {
                "id": "450025",
                "name": "выше нуля ",
                "correct": false
            },
            {
                "id": "450027",
                "name": "не нормируется",
                "correct": false
            },
            {
                "id": "450026",
                "name": "не ниже плюс 5 °С",
                "correct": true
            },
            {
                "id": "450028",
                "name": "не ниже плюс 15 °С",
                "correct": false
            }
        ]
    },
    {
        "id": "438039",
        "name": "Допускается ли участие в проведении\nэкспертизы нескольких экспертов, аттестованных \nпо одному направлению деятельности? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438044",
                "name": "да, но только в случае если второй эксперт по соответствующему направлению деятельности является внештатным сотрудником",
                "correct": false
            },
            {
                "id": "438045",
                "name": "да, в проведении экспертизы могут участвовать два и более эксперта, аттестованных по одному направлению деятельности",
                "correct": true
            },
            {
                "id": "438041",
                "name": "да, допускается без ограничений",
                "correct": true
            },
            {
                "id": "438040",
                "name": "да, но в таком случае заключение экспертизы подписывает только один из экспертов, аттестованных по соответствующему направлению деятельности",
                "correct": false
            },
            {
                "id": "438043",
                "name": "нет",
                "correct": false
            },
            {
                "id": "438042",
                "name": "да, но не более двух экспертов по одному направлению деятельности",
                "correct": false
            }
        ]
    },
    {
        "id": "437727",
        "name": "Что является объектом технического регулирования?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437733",
                "name": "строительные материалы и изделия для изготовления, возведения и (или) монтажа строительных конструкций и систем инженерного обеспечения зданий и сооружений",
                "correct": false
            },
            {
                "id": "437730",
                "name": "временные здания и сооружения, не являющиеся объектами капитального строительства",
                "correct": false
            },
            {
                "id": "437729",
                "name": "здания и сооружения любого назначения (в том числе входящие в их состав сети инженерно-технического обеспечения и системы инженерно-технического обеспечения)",
                "correct": true
            },
            {
                "id": "437728",
                "name": "технологические процессы, осуществляемые в зданиях и сооружениях в соответствии с их функциональным назначением, а также располагаемое в них технологическое оборудование",
                "correct": false
            },
            {
                "id": "437732",
                "name": "осуществляемые на всех этапах жизненного цикла зданий и сооружений любого назначения процессы инженерных изысканий, архитектурно - строительного проектирования, строительства, реконструкции, капитального ремонта, монтажа, наладки, эксплуатации (включая текущий ремонт), сноса",
                "correct": true
            },
            {
                "id": "437731",
                "name": "требования охраны труда в строительстве, а также при эксплуатации и ликвидации зданий и сооружений",
                "correct": false
            }
        ]
    },
    {
        "id": "438012",
        "name": "Какие признаки относятся к обязательным для идентификации зданий и сооружений в соответствии с Федеральным законом от 30.12.2009 № 384-ФЗ \"Технический регламент о безопасности зданий и сооружений\"?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438014",
                "name": "принадлежность к объектам транспортной инфраструктуры и к другим объектам, функционально-технологические особенности которых влияют на их безопасность",
                "correct": true
            },
            {
                "id": "438016",
                "name": "пожарная и взрывопожарная опасность",
                "correct": true
            },
            {
                "id": "438013",
                "name": "протяженность (в случае строительства линейного объекта)",
                "correct": false
            },
            {
                "id": "438018",
                "name": "класс энергоэффективности",
                "correct": false
            },
            {
                "id": "438015",
                "name": "количество этажей",
                "correct": false
            },
            {
                "id": "438017",
                "name": "наличие помещений с постоянным пребыванием людей",
                "correct": true
            }
        ]
    },
    {
        "id": "450579",
        "name": "Основная система уравнивания потенциалов в электроустановках до 1 кВ, в соответствии с ПУЭ, должна соединять между собой следующие проводящие части:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450582",
                "name": "металлические трубы коммуникаций, входящих в здание",
                "correct": true
            },
            {
                "id": "450581",
                "name": "заземляющий проводник, присоединённый к заземлителю повторного заземления на вводе в здание (если есть заземлитель)",
                "correct": true
            },
            {
                "id": "450580",
                "name": "нулевой защитный РЕ- или РЕN-проводник питающей линии в системе ТN",
                "correct": true
            },
            {
                "id": "450583",
                "name": "металлические части каркаса здания",
                "correct": true
            },
            {
                "id": "450585",
                "name": "заземляющее устройство системы молниезащиты 2-й и 3-й категорий",
                "correct": true
            },
            {
                "id": "450584",
                "name": "металлические части централизованных систем вентиляции кондиционирования ",
                "correct": true
            }
        ]
    },
    {
        "id": "450520",
        "name": "На сколько необходимо предусматривать повышение средней освещенности на наземных пешеходных переходах улиц и дорог категорий А и Б по сравнению с нормой освещенности на пересекаемой проезжей части в соответствии с СП 52.13330.2016?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450523",
                "name": "не следует предусматривать повышение средней освещенности",
                "correct": false
            },
            {
                "id": "450522",
                "name": "следует предусматривать повышение средней освещенности не менее чем в 2 раза",
                "correct": false
            },
            {
                "id": "450521",
                "name": "следует предусматривать повышение средней освещенности не менее чем в 1,5 раза",
                "correct": true
            },
            {
                "id": "450524",
                "name": "следует предусматривать повышение средней освещенности не менее чем на 20%",
                "correct": false
            }
        ]
    },
    {
        "id": "450514",
        "name": "Какой должна быть освещенность на входных площадках, в универсальных кабинах санузлов и душевых, на путях эвакуации, на открытых лестницах, пандусах и в пожаробезопасных зонах, доступных для маломобильных групп населения, в соответствии с   СП 59.13330.2020? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450516",
                "name": "не менее 50 лк",
                "correct": false
            },
            {
                "id": "450519",
                "name": "не должна превышать 300 лк",
                "correct": false
            },
            {
                "id": "450518",
                "name": "не должна превышать 100 лк",
                "correct": false
            },
            {
                "id": "450515",
                "name": "не менее 100 лк",
                "correct": true
            },
            {
                "id": "450517",
                "name": "не менее 75 лк",
                "correct": false
            }
        ]
    },
    {
        "id": "450082",
        "name": "Нормы освещенности помещений жилых и общественных зданий, в соответствии с СП 52.13330.2016, следует повышать на одну ступень шкалы освещенности в следующих случаях:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450085",
                "name": "при повышенных требованиях к насыщенности помещения светом для зрительных работ разрядов Г-Е (зрительные и концертные залы, фойе уникальных зданий и т.п.)",
                "correct": true
            },
            {
                "id": "450086",
                "name": "при применении системы комбинированного освещения административных зданий (кабинеты, рабочие комнаты, читальные залы библиотеки)",
                "correct": true
            },
            {
                "id": "450087",
                "name": "в помещениях, где более половины работающих старше 40 лет",
                "correct": true
            },
            {
                "id": "450088",
                "name": "в помещениях вестибюлей, лестниц, лифтовых холлов, приквартирных коридоров жилых зданий",
                "correct": false
            },
            {
                "id": "450083",
                "name": "при зрительных работах разрядов А-В при специальных повышенных санитарных требованиях (например, в некоторых помещениях общественного питания и торговли)",
                "correct": true
            },
            {
                "id": "450084",
                "name": "при отсутствии в помещении с постоянным пребыванием людей естественного света",
                "correct": true
            }
        ]
    },
    {
        "id": "437611",
        "name": "Где и кем указываются идентификационные признаки зданий и сооружений?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437614",
                "name": "указываются собственником здания или сооружения в исполнительной документации",
                "correct": false
            },
            {
                "id": "437617",
                "name": "указываются застройщиком (заказчиком) в задании на проектирование",
                "correct": true
            },
            {
                "id": "437612",
                "name": "указываются застройщиком (заказчиком) в договорной документации по выполнению инженерных изысканий для строительства здания или сооружения",
                "correct": false
            },
            {
                "id": "437616",
                "name": "указываются лицом, осуществляющим подготовку проектной документации в текстовых материалах в составе проектной документации, передаваемой по окончании строительства на хранение собственнику здания или сооружения",
                "correct": true
            },
            {
                "id": "437613",
                "name": "указываются застройщиком (заказчиком) в задании на выполнение инженерных изысканий для строительства здания или сооружения",
                "correct": true
            },
            {
                "id": "437615",
                "name": "указываются застройщиком (заказчиком) в договорной документации на проектирование здания или сооружения",
                "correct": false
            }
        ]
    },
    {
        "id": "437755",
        "name": "Какие решения должны определять материалы в составе проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437759",
                "name": "архитектурные",
                "correct": true
            },
            {
                "id": "437758",
                "name": "математические",
                "correct": false
            },
            {
                "id": "437760",
                "name": "конструктивные",
                "correct": true
            },
            {
                "id": "437757",
                "name": "обоснованные",
                "correct": false
            },
            {
                "id": "437761",
                "name": "организационные",
                "correct": false
            },
            {
                "id": "437756",
                "name": "функционально-технологические",
                "correct": true
            }
        ]
    },
    {
        "id": "450288",
        "name": "Что входит в определение термина «главная заземляющая шина»? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450290",
                "name": "шина, предназначенная для присоединения нескольких проводников с целью заземления и уравнивания потенциалов",
                "correct": true
            },
            {
                "id": "450294",
                "name": "шина, установленная внутри вводного устройства и обозначенная желто-зелеными полосами",
                "correct": false
            },
            {
                "id": "450291",
                "name": "шина, являющаяся частью заземляющего устройства электроустановки выше 1 кВ",
                "correct": false
            },
            {
                "id": "450292",
                "name": "алюминиевая шина, являющаяся частью заземляющего устройства электроустановки до 1 кВ",
                "correct": false
            },
            {
                "id": "450293",
                "name": "шина, являющаяся частью заземляющего устройства электроустановки до 1 кВ",
                "correct": true
            },
            {
                "id": "450289",
                "name": "шина, предназначенная для присоединения нескольких проводников с целью выравнивания потенциалов",
                "correct": false
            }
        ]
    },
    {
        "id": "450202",
        "name": "Какие меры защиты при косвенном прикосновении должны быть применены для защиты от поражения электрическим током в случае повреждения изоляции?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450208",
                "name": "защитное электрическое разделение цепей",
                "correct": true
            },
            {
                "id": "450207",
                "name": "двойная или усиленная изоляция",
                "correct": true
            },
            {
                "id": "450204",
                "name": "автоматическое отключение питания",
                "correct": true
            },
            {
                "id": "450206",
                "name": "выравнивание потенциалов",
                "correct": true
            },
            {
                "id": "450203",
                "name": "защитное заземление",
                "correct": true
            },
            {
                "id": "450205",
                "name": "уравнивание потенциалов",
                "correct": true
            }
        ]
    },
    {
        "id": "437389",
        "name": "Что из перечисленного является основанием для отказа в принятии проектной документации и (или) результатов инженерных изысканий, представленных на государственную экспертизу?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437392",
                "name": "государственная экспертиза должна осуществляться иной организацией по проведению государственной экспертизы",
                "correct": true
            },
            {
                "id": "437393",
                "name": "направление не подлежащих государственной экспертизе проектной документации и (или) результатов инженерных изысканий",
                "correct": false
            },
            {
                "id": "437390",
                "name": "документы предоставлены на бумажном носителе, в случае, когда проектная документация и (или) результаты инженерных изысканий содержат сведения, составляющие государственную тайну",
                "correct": false
            },
            {
                "id": "437391",
                "name": "документы предоставлены на бумажном носителе, в случае, когда проектная документация и (или) результаты инженерных изысканий не содержат сведения, составляющие государственную тайну",
                "correct": false
            },
            {
                "id": "437394",
                "name": "несоответствие результатов инженерных изысканий составу и форме, установленным законодательством о градостроительной деятельности",
                "correct": true
            },
            {
                "id": "437395",
                "name": "отсутствие положительного заключения государственной экспертизы результатов инженерных изысканий (в случае, если проектная документация направлена на государственную экспертизу после государственной экспертизы результатов инженерных изысканий)",
                "correct": true
            }
        ]
    },
    {
        "id": "450237",
        "name": "Какое расстояние должно быть в свету между СИП и стеной здания или сооружения для ВЛ напряжением до 1 кВ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450238",
                "name": "не менее 0,02 метра",
                "correct": false
            },
            {
                "id": "450239",
                "name": "не менее 0,04 метра",
                "correct": false
            },
            {
                "id": "450240",
                "name": "не менее 0,06 метров",
                "correct": true
            },
            {
                "id": "450241",
                "name": "не менее 0,08 метра",
                "correct": false
            },
            {
                "id": "450242",
                "name": "не менее 0,1 метра",
                "correct": false
            },
            {
                "id": "450243",
                "name": "не менее 0,01 метра",
                "correct": false
            }
        ]
    },
    {
        "id": "450269",
        "name": "Как учитывается мощность резервных электродвигателей, а также электроприемников противопожарных устройств и уборочных механизмов при расчете электрических нагрузок питающих линий и вводов в здание?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450273",
                "name": "не учитывается, за исключением тех случаев, когда она определяет выбор защитных аппаратов",
                "correct": true
            },
            {
                "id": "450271",
                "name": "учитывается с коэффициентом несовпадения максимумов ",
                "correct": false
            },
            {
                "id": "450274",
                "name": "не учитывается, за исключением тех случаев, когда она определяет выбор сечений проводников",
                "correct": true
            },
            {
                "id": "450272",
                "name": "учитывается для нагрузки первой категории надежности",
                "correct": false
            },
            {
                "id": "450275",
                "name": "не учитывается во всех случаях",
                "correct": false
            },
            {
                "id": "450270",
                "name": "учитывается с коэффициентом спроса",
                "correct": false
            }
        ]
    },
    {
        "id": "437334",
        "name": "Какие характеристики указывают на необходимость отнесения сооружения связи к особо опасным, технически сложным сооружениям связи?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437340",
                "name": "высота более 100 м",
                "correct": false
            },
            {
                "id": "437339",
                "name": "заглубление подземной части (полностью или частично) ниже планировочной отметки земли от 5 м до 10 м",
                "correct": true
            },
            {
                "id": "437336",
                "name": "высота от 75 м до 100 м",
                "correct": true
            },
            {
                "id": "437335",
                "name": "заглубление подземной части ниже планировочной отметки земли более 15 м",
                "correct": false
            },
            {
                "id": "437338",
                "name": "заглубление подземной части (полностью или частично) ниже планировочной отметки земли до 5 м",
                "correct": false
            },
            {
                "id": "437337",
                "name": "высота до 75 м",
                "correct": false
            }
        ]
    },
    {
        "id": "450121",
        "name": "Для насоса в сети напряжением 10 кВ с изолированной нейтралью, установленного в производственном здании определить необходимость выполнения защитного заземления для двигателя данной электроустановки?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450124",
                "name": "не нужно ",
                "correct": false
            },
            {
                "id": "450125",
                "name": "нужно только при условии высоких значений токов замыкания на землю",
                "correct": false
            },
            {
                "id": "450123",
                "name": "не нужно, при условии выполнения контура заземления молниезащиты",
                "correct": false
            },
            {
                "id": "450122",
                "name": "нужно ",
                "correct": true
            }
        ]
    },
    {
        "id": "438052",
        "name": "В каких случаях в отношении изменений, внесенных в проектную документацию, получившую положительное заключение экспертизы, не требуется проведение повторной экспертизы? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438057",
                "name": "государственная экспертиза проектной документации по решению застройщика может не проводиться если изменения внесены в проектную документацию после получения разрешения на строительство объекта",
                "correct": false
            },
            {
                "id": "438055",
                "name": "государственная экспертиза проектной документации по решению застройщика может не проводиться если изменения соответствуют требованиям, указанным в пунктах 1 - 5 части 3.8 статьи 49 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "438053",
                "name": "любые изменения, внесенные в проектную документацию, могут быть утверждены застройщиком (техническим заказчиком) при наличии подтверждения, предоставленного лицом, являющимся членом саморегулируемой организации, основанной на членстве лиц, осуществляющих подготовку проектной документации, утвержденного специалистом по организации архитектурно-строительного проектирования в должности главного инженера проекта",
                "correct": false
            },
            {
                "id": "438056",
                "name": "изменения, внесенные в проектную документацию, могут быть утверждены застройщиком (техническим заказчиком) при наличии заключения о согласовании разделов проектной документации в рамках экспертного сопровождения до направления проектной документации на государственную экспертизу",
                "correct": false
            },
            {
                "id": "438054",
                "name": "государственная экспертиза проектной документации по решению застройщика может не проводиться если изменения одновременно соответствуют требованиям, указанным в пунктах 2 - 4 части 3.8 статьи 49 Градостроительного кодекса Российской Федерации, связаны с заменой строительных ресурсов на аналоги и не приводят к увеличению сметной стоимости строительства, реконструкции, капитального ремонта более чем на 30 процентов и свыше 100 млн. рублей",
                "correct": true
            }
        ]
    },
    {
        "id": "450627",
        "name": "Каковы допустимые сечения самонесущих изолированных проводов на магистрали ВЛИ и линейном ответвлении от ВЛИ до 1 кВ по условиям механической прочности в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450629",
                "name": "сечение несущей жилы – 35 мм² при нормативной стенке гололёда 10 мм",
                "correct": true
            },
            {
                "id": "450631",
                "name": "сечение жил СИП, скрученных в жгут, без несущего провода - 16 мм² при нормативной стенке гололёда 10 мм",
                "correct": false
            },
            {
                "id": "450630",
                "name": "сечение несущей жилы – 50 мм² при нормативной стенке гололёда 15 мм и более",
                "correct": true
            },
            {
                "id": "450628",
                "name": "сечение несущей жилы – 25 мм² при нормативной стенке гололёда 5 мм",
                "correct": false
            },
            {
                "id": "450632",
                "name": "сечение жил СИП, скрученных в жгут, без несущего провода - 25 мм² при нормативной стенке гололёда 15 мм и более ",
                "correct": true
            },
            {
                "id": "450633",
                "name": "сечение жил СИП, скрученных в жгут, без несущего провода - 35 мм² при нормативной стенке гололёда 15 мм и более ",
                "correct": true
            }
        ]
    },
    {
        "id": "450420",
        "name": "Какие условия, в соответствии с ПУЭ, должны быть одновременно соблюдены при отнесении к числу независимых источников питания двух секций или систем шин одной или двух электростанций и подстанций?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450423",
                "name": "к числу независимых источников питания относятся две секции или системы шин нескольких электростанций и подстанций",
                "correct": false
            },
            {
                "id": "450424",
                "name": "к числу независимых источников питания относятся две секции или системы шин одной или двух электростанций и подстанций если каждая из секций или систем шин в свою очередь имеет питание от независимого источника питания",
                "correct": false
            },
            {
                "id": "450421",
                "name": "к числу независимых источников питания относятся две секции или системы шин одной или двух электростанций и подстанций при одновременном соблюдении следующих двух условий: 1) каждая из секций или систем шин в свою очередь имеет питание от независимого источника питания; 2) секции (системы) шин не связаны между собой или имеют связь, автоматически отключающуюся при нарушении нормальной работы одной из секций (систем) шин",
                "correct": true
            },
            {
                "id": "450422",
                "name": "к числу независимых источников питания относятся две секции или системы шин одной или двух электростанций и подстанций",
                "correct": false
            }
        ]
    },
    {
        "id": "450446",
        "name": "Аварийное освещение это:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450450",
                "name": "освещение для эвакуации людей или завершения потенциально опасного процесса",
                "correct": false
            },
            {
                "id": "450448",
                "name": "естественное освещение помещения через световые проемы в наружных стенах",
                "correct": false
            },
            {
                "id": "450449",
                "name": "освещение опасных пространств",
                "correct": false
            },
            {
                "id": "450447",
                "name": "освещение, предусматриваемое в случае выхода из строя питания рабочего освещения",
                "correct": true
            }
        ]
    },
    {
        "id": "450641",
        "name": "Какие климатические условия должны учитываться при расчёте ВЛ и их элементов в соответствии с ПУЭ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450642",
                "name": "ветровое давление",
                "correct": true
            },
            {
                "id": "450646",
                "name": "температура воздуха",
                "correct": true
            },
            {
                "id": "450644",
                "name": "толщина стенки гололёда",
                "correct": true
            },
            {
                "id": "450647",
                "name": "интенсивность грозовой деятельности",
                "correct": true
            },
            {
                "id": "450645",
                "name": "высота снежного покрова",
                "correct": false
            },
            {
                "id": "450643",
                "name": "расчётный суточный максимум осадков",
                "correct": false
            }
        ]
    },
    {
        "id": "437953",
        "name": "В целях реализации каких решений, содержащихся в проектной документации, разрабатывается рабочая документация?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437956",
                "name": "экономические решения",
                "correct": false
            },
            {
                "id": "437954",
                "name": "технологические решения",
                "correct": true
            },
            {
                "id": "437957",
                "name": "технические решения",
                "correct": true
            },
            {
                "id": "437959",
                "name": "управленческие решения",
                "correct": false
            },
            {
                "id": "437958",
                "name": "архитектурные решения",
                "correct": true
            },
            {
                "id": "437955",
                "name": "организационные решения",
                "correct": false
            }
        ]
    },
    {
        "id": "450244",
        "name": "Какое расстояние должно быть в свету между кабелем и стенкой канала теплопровода при параллельной прокладке?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450248",
                "name": "не менее 2 метров",
                "correct": true
            },
            {
                "id": "450246",
                "name": "не менее 0,5 метра",
                "correct": false
            },
            {
                "id": "450245",
                "name": "не менее 0,25 метра",
                "correct": false
            },
            {
                "id": "450247",
                "name": "не менее 1 метра",
                "correct": false
            }
        ]
    },
    {
        "id": "437396",
        "name": "Что из перечисленного является основанием для отказа в принятии проектной документации и (или) результатов инженерных изысканий, представленных на государственную экспертизу?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437401",
                "name": "направление не подлежащей государственной экспертизе проектной документации и (или) результатов инженерных изысканий",
                "correct": false
            },
            {
                "id": "437398",
                "name": "непредставление положительного заключения государственной экологической экспертизы в случае проведения государственной экологической экспертизы проектной документации, подлежащей государственной экологической экспертизе в соответствии с законодательством Российской Федерации",
                "correct": false
            },
            {
                "id": "437397",
                "name": "отсутствие в составе проектной документации разделов, которые подлежат включению в состав такой документации в соответствии с требованиями, предусмотренными законодательством о градостроительной деятельности",
                "correct": true
            },
            {
                "id": "437402",
                "name": "непредставление ведомости объемов работ, учтенных в сметных расчетах, в случаях, предусмотренных законодательством о градостроительной деятельности",
                "correct": true
            },
            {
                "id": "437399",
                "name": "непредставление выписки из реестра членов саморегулируемой организации в области архитектурно-строительного проектирования и (или) инженерных изысканий, членом которой является исполнитель работ по подготовке проектной документации и (или) выполнению инженерных изысканий, действительной на дату передачи проектной документации и (или) результатов инженерных изысканий застройщику (техническому заказчику), в случаях, предусмотренных законодательством о градостроительной деятельности",
                "correct": false
            },
            {
                "id": "437400",
                "name": "непредставление документов, подтверждающих полномочия заявителя действовать от имени застройщика, технического заказчика, лица, обеспечившего выполнение инженерных изысканий и (или) подготовку проектной документации в случаях, предусмотренных частями 1.1 и 1.2 статьи 48 Градостроительного кодекса Российской Федерации (если заявитель не является техническим заказчиком, застройщиком, лицом, обеспечившим выполнение инженерных изысканий и (или) подготовку проектной документации в случаях, предусмотренных частями 1.1 и 1.2 статьи 48 Градостроительного кодекса Российской Федерации), в которых полномочия на заключение, изменение, исполнение, расторжение договора о проведении государственной экспертизы должны быть оговорены специально",
                "correct": true
            }
        ]
    },
    {
        "id": "437534",
        "name": "На какие виды подразделяются объекты капитального строительства в зависимости от функционального назначения и характерных признаков согласно постановлению Правительства Российской Федерации от 16.02.2008 № 87?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437538",
                "name": "объекты непроизводственного назначения (здания, строения, сооружения жилищного фонда, социально-культурного и коммунально-бытового назначения, а также иные объекты капитального строительства непроизводственного назначения)",
                "correct": true
            },
            {
                "id": "437537",
                "name": "объекты воздушного и водного транспорта",
                "correct": false
            },
            {
                "id": "437539",
                "name": "линейные объекты (трубопроводы, автомобильные и железные дороги, линии электропередачи и др.)",
                "correct": true
            },
            {
                "id": "437540",
                "name": "объекты стратегического значения",
                "correct": false
            },
            {
                "id": "437536",
                "name": "строящиеся и реконструируемые объекты",
                "correct": false
            },
            {
                "id": "437535",
                "name": "объекты производственного назначения (здания, строения, сооружения производственного назначения)",
                "correct": true
            }
        ]
    },
    {
        "id": "438144",
        "name": "В каких случаях и в каком порядке допускается оперативное внесение изменений в ходе проведения оценки соответствия?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438147",
                "name": "при проведении экспертного сопровождения, в том числе при подготовке заключения государственной экспертизы по результатам экспертного сопровождения, оперативное внесение изменений не предусмотрено",
                "correct": false
            },
            {
                "id": "438145",
                "name": "оперативное внесение изменений в проектную документацию и (или) результаты инженерных изысканий осуществляется в сроки и в порядке, которые установлены договором",
                "correct": true
            },
            {
                "id": "438146",
                "name": "при проведении экспертизы проектной документации и результатов инженерных изысканий может осуществляться оперативное внесение изменений в заявление о проведении государственной экспертизы",
                "correct": true
            },
            {
                "id": "438149",
                "name": "при проведении экспертизы проектной документации и результатов инженерных изысканий может осуществляться оперативное внесение изменений, но не позднее чем за 5 рабочих дней до окончания срока проведения государственной экс",
                "correct": false
            },
            {
                "id": "438150",
                "name": "при проведении экспертизы проектной документации и результатов инженерных изысканий не может осуществляться оперативное внесение изменений в разделы проектной документации, в отношении которых получено заключение о согласовании разделов проектной документации в рамках экспертного сопровождения до направления проектной документации на экспертизу",
                "correct": false
            },
            {
                "id": "438148",
                "name": "при проведении экспертного сопровождения, в том числе при подготовке заключения государственной экспертизы по результатам экспертного сопровождения, предусмотрено оперативное внесение изменений только в результаты инженерных изысканий",
                "correct": false
            }
        ]
    },
    {
        "id": "437999",
        "name": "Предметом экспертной оценки разделов проектной документации, проводимой в рамках экспертного сопровождения разделов проектной документации объекта капитального строительства в соответствии с Положением о порядке экспертного сопровождения результатов инженерных изысканий и (или) разделов проектной документации объекта капитального строительства, утвержденным постановлением Правительства Российской Федерации от 6 мая 2023 г. № 717 \"Об утверждении Положения о порядке экспертного сопровождения результатов инженерных изысканий и (или) разделов проектной документации объекта капитального строительства, внесении изменений в некоторые акты Правительства Российской Федерации и признании утратившими силу отдельных положений некоторых актов Правительства Российской Федерации\",\nявляется:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438001",
                "name": "оценка соответствия разделов проектной документации объекта капитального строительства, санитарно-эпидемиологическим требованиям",
                "correct": true
            },
            {
                "id": "438002",
                "name": "оценка соответствия разделов проектной документации объекта капитального строительства требованиям антитеррористической защищенности объекта",
                "correct": true
            },
            {
                "id": "438003",
                "name": "изучение и оценка расчетов, содержащихся в сметной документации, в целях установления их соответствия утвержденным сметным нормативам, единичным расценкам, в том числе их отдельным составляющим, к сметным нормам, индексам изменения сметной стоимости, информация о которых включена в федеральный реестр сметных нормативов",
                "correct": false
            },
            {
                "id": "438004",
                "name": "оценка соответствия разделов проектной документации объекта капитального строительства требованиям в области охраны окружающей среды",
                "correct": true
            },
            {
                "id": "438000",
                "name": "оценка соответствия разделов проектной документации объекта капитального строительства требованиям технических регламентов",
                "correct": true
            }
        ]
    },
    {
        "id": "449990",
        "name": "Уменьшение глубины прокладки кабеля допускается до 0,5 м в следующих случаях:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "449992",
                "name": "при прокладке по пахотным землям",
                "correct": false
            },
            {
                "id": "449996",
                "name": "в охранной зоне воздушных линий электропередачи",
                "correct": false
            },
            {
                "id": "449994",
                "name": "в местах пересечения с подземными сооружениями",
                "correct": false
            },
            {
                "id": "449991",
                "name": "на участках длиной до 5 м при вводе в здания",
                "correct": true
            },
            {
                "id": "449995",
                "name": "в местах пересечения с подземными сооружениями при условии защиты кабелей от механических повреждений",
                "correct": true
            },
            {
                "id": "449993",
                "name": "при пересечении площадей",
                "correct": false
            }
        ]
    },
    {
        "id": "437720",
        "name": "Что включают в себя требования энергетической эффективности зданий, строений, сооружений?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437721",
                "name": "требования к отдельным элементам, конструкциям зданий, строений, сооружений и к их свойствам, позволяющие исключить нерациональный расход энергетических ресурсов как в процессе строительства, реконструкции, капитального ремонта зданий, строений, сооружений, так и в процессе их эксплуатации",
                "correct": true
            },
            {
                "id": "437724",
                "name": "требования к используемым в зданиях, строениях, сооружениях устройствам и технологиям, позволяющие исключить нерациональный расход энергетических ресурсов как в процессе строительства, реконструкции, капитального ремонта зданий, строений, сооружений, так и в процессе их эксплуатации",
                "correct": true
            },
            {
                "id": "437726",
                "name": "требования к включаемым в проектную документацию и применяемым при строительстве, реконструкции, капитальном ремонте зданий, строений, сооружений технологиям и материалам, позволяющие исключить нерациональный расход энергетических ресурсов как в процессе строительства, реконструкции, капитального ремонта зданий, строений, сооружений, так и в процессе их эксплуатации",
                "correct": true
            },
            {
                "id": "437725",
                "name": "требования по интеграции в энергетический баланс зданий, строений, сооружений нетрадиционных источников энергии и вторичных энергоресурсов",
                "correct": false
            },
            {
                "id": "437722",
                "name": "показатели, характеризующие удельную величину расхода энергетических ресурсов в здании, строении, сооружении",
                "correct": true
            },
            {
                "id": "437723",
                "name": "требования к влияющим на энергетическую эффективность зданий, строений, сооружений архитектурным, функционально-технологическим, конструктивным и инженерно-техническим решениям",
                "correct": true
            }
        ]
    },
    {
        "id": "437974",
        "name": "Какие сведения о потребности объекта капитального строительства в инженерно-техническом обеспечении должны быть приведены в разделе «пояснительная записка» для объектов производственного и непроизводственного назначения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437976",
                "name": "в топливе",
                "correct": true
            },
            {
                "id": "437980",
                "name": "в электрической энергии",
                "correct": true
            },
            {
                "id": "437977",
                "name": "в воде",
                "correct": true
            },
            {
                "id": "437979",
                "name": "в газе",
                "correct": true
            },
            {
                "id": "437975",
                "name": "в емкости присоединяемой сети связи",
                "correct": false
            },
            {
                "id": "437978",
                "name": "в расчетном объеме дождевых стоков",
                "correct": false
            }
        ]
    },
    {
        "id": "437500",
        "name": "Какие сведения единого государственного реестра заключений экспертизы проектной документации объектов капитального строительства относятся к сведениям, доступ к которым обеспечивается всем заинтересованным лицам на бесплатной основе в информационно-телекоммуникационной сети \"Интернет\" в форме открытых данных?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437503",
                "name": "сведения об экспертной организации",
                "correct": true
            },
            {
                "id": "437501",
                "name": "сведения об индивидуальных предпринимателях и (или) юридических лицах, подготовивших проектную документацию, по результатам рассмотрения которой подготовлено заключение экспертизы",
                "correct": true
            },
            {
                "id": "437505",
                "name": "сведения об объекте экспертизы (проектная документация и результаты инженерных изысканий, по результатам рассмотрения которых подготовлено заключение экспертизы)",
                "correct": true
            },
            {
                "id": "437504",
                "name": "наименование и адрес (местоположение) объекта капитального строительства, применительно к которому подготовлена проектная документация",
                "correct": true
            },
            {
                "id": "437502",
                "name": "сведения о застройщике (техническом заказчике), обеспечившем подготовку проектной документации, по результатам рассмотрения которой подготовлено заключение экспертизы",
                "correct": true
            },
            {
                "id": "437506",
                "name": "сведения об источнике финансирования",
                "correct": false
            }
        ]
    },
    {
        "id": "450276",
        "name": "На какой высоте от уровня пола, над дверными проемами эвакуационных выходов, следует устанавливать световые указатели со знаком безопасности «Выход»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450278",
                "name": "на высоте 2,0 м – 2,1 м",
                "correct": false
            },
            {
                "id": "450280",
                "name": "на высоте 2,2 м – 2,3 м",
                "correct": false
            },
            {
                "id": "450277",
                "name": "на высоте 1,9 м – 2,0 м",
                "correct": false
            },
            {
                "id": "450279",
                "name": "на высоте 2,1 м – 2,2 м",
                "correct": true
            }
        ]
    },
    {
        "id": "450004",
        "name": "Какое расстояние должно быть от провода ВЛ 0,4 кВ до элементов трубопровода при пересечении ВЛ с надземным трубопроводом?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450009",
                "name": "не менее 3 м",
                "correct": false
            },
            {
                "id": "450008",
                "name": "пересечение с надземным трубопроводом не разрешено",
                "correct": false
            },
            {
                "id": "450006",
                "name": "не более 2 м ",
                "correct": false
            },
            {
                "id": "450010",
                "name": "не нормируется",
                "correct": false
            },
            {
                "id": "450007",
                "name": "0,5 м",
                "correct": false
            },
            {
                "id": "450005",
                "name": "не менее 1 м ",
                "correct": true
            }
        ]
    },
    {
        "id": "450430",
        "name": "Для какой защиты, в соответствии с ПУЭ, в электроустановках напряжением до 1 кВ применяются устройства защитного отключения (УЗО) с номинальным отключающим дифференциальным током не более 30 мА?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450435",
                "name": "уравнивание потенциалов",
                "correct": false
            },
            {
                "id": "450432",
                "name": "основная изоляция токоведущих частей",
                "correct": false
            },
            {
                "id": "450431",
                "name": "применение сверхнизкого (малого) напряжения",
                "correct": false
            },
            {
                "id": "450434",
                "name": "для защиты от коротких замыканий",
                "correct": false
            },
            {
                "id": "450433",
                "name": "дополнительной защиты от прямого прикосновения в электроустановках напряжением до 1 кВ",
                "correct": true
            }
        ]
    },
    {
        "id": "450114",
        "name": "Где разрешается размещать ВРУ и ГРЩ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450116",
                "name": "в специально выделенных запирающихся помещениях (электрощитовых), доступных только для обслуживающего персонала",
                "correct": true
            },
            {
                "id": "450117",
                "name": "непосредственно над жилыми комнатами",
                "correct": false
            },
            {
                "id": "450119",
                "name": "под уборными, ванными комнатами, душевыми обычной конструкции перекрытий",
                "correct": false
            },
            {
                "id": "450120",
                "name": "в сухих подвалах при условии, что эти помещения отделены противопожарными перегородками 1-го типа",
                "correct": true
            },
            {
                "id": "450115",
                "name": "на незадымляемых лестничных клетках",
                "correct": false
            },
            {
                "id": "450118",
                "name": "непосредственно под жилыми комнатами",
                "correct": false
            }
        ]
    },
    {
        "id": "450396",
        "name": "Какие электроприемники относятся к электроприемникам первой категории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450398",
                "name": "электроприемники, перерыв электроснабжения которых приводит к массовому недоотпуску продукции, массовым простоям рабочих, механизмов и промышленного транспорта, нарушению нормальной деятельности значительного количества городских и сельских жителей",
                "correct": false
            },
            {
                "id": "450399",
                "name": "электроприемники, бесперебойная работа которых необходима для безаварийного останова производства в целях предотвращения угрозы жизни людей, взрывов и пожаров",
                "correct": false
            },
            {
                "id": "450397",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой: опасность для жизни людей, угрозу для безопасности государства, значительный материальный ущерб, расстройство сложного технологического процесса, нарушение функционирования особо важных элементов коммунального хозяйства, объектов связи и телевидения",
                "correct": true
            }
        ]
    },
    {
        "id": "437748",
        "name": "Что включает в себя проектная документация?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437749",
                "name": "материалы в текстовой форме",
                "correct": true
            },
            {
                "id": "437752",
                "name": "материалы в форме информационной модели",
                "correct": true
            },
            {
                "id": "437751",
                "name": "материалы в графической форме",
                "correct": true
            },
            {
                "id": "437753",
                "name": "карту функциональных зон",
                "correct": false
            },
            {
                "id": "437750",
                "name": "сведения о границах публичных сервитутов",
                "correct": false
            },
            {
                "id": "437754",
                "name": "карту границ населенных пунктов",
                "correct": false
            }
        ]
    },
    {
        "id": "437920",
        "name": "Кем осуществляется согласование проектной документации на проведение работ по сохранению объекта культурного наследия?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437924",
                "name": "муниципальным органом охраны объектов культурного наследия - в отношении объектов культурного наследия местного (муниципального) значения",
                "correct": true
            },
            {
                "id": "437926",
                "name": "юридическими лицами, аккредитованными на право проведения негосударственной экспертизы",
                "correct": false
            },
            {
                "id": "437922",
                "name": "федеральным органом исполнительной власти, осуществляющим функции по выработке и реализации государственной политики и нормативно-правовому регулированию в сфере строительства, архитектуры, градостроительства",
                "correct": false
            },
            {
                "id": "437925",
                "name": "органом исполнительной власти субъекта Российской Федерации, уполномоченными на проведение государственной экспертизы проектной документации",
                "correct": false
            },
            {
                "id": "437921",
                "name": "федеральным органом охраны объектов культурного наследия - в отношении отдельных объектов культурного наследия федерального значения, перечень которых утверждается Правительством Российской Федерации",
                "correct": true
            },
            {
                "id": "437923",
                "name": "региональным органом охраны объектов культурного наследия - в отношении объектов культурного наследия федерального значения (за исключением отдельных объектов культурного наследия федерального значения, перечень которых утверждается Правительством Российской Федерации), объектов культурного наследия регионального значения, выявленных объектов культурного наследия",
                "correct": true
            }
        ]
    },
    {
        "id": "449976",
        "name": "Транзитная прокладка каких коммуникаций допустима через лифтовые шахты?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "449978",
                "name": "допускается прокладка кабелей и проводов за исключением электропроводки для слаботочных устройств",
                "correct": false
            },
            {
                "id": "449979",
                "name": "запрещается размещение электропроводки в шахтах лифтов, за исключением электропроводки, обслуживающей лифты",
                "correct": true
            },
            {
                "id": "449980",
                "name": "допускается прокладка питающих линий электроприемников противопожарных устройств",
                "correct": false
            },
            {
                "id": "449982",
                "name": "допускается прокладка сетей сечением до 16 кв.мм",
                "correct": false
            },
            {
                "id": "449977",
                "name": "сети освещения шахт лифтов",
                "correct": true
            },
            {
                "id": "449981",
                "name": "допускается прокладка слаботочных сетей",
                "correct": false
            }
        ]
    },
    {
        "id": "450281",
        "name": "Какие электроприемники относятся к электроприемникам первой категории? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450282",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой расстройство сложного технологического процесса",
                "correct": true
            },
            {
                "id": "450284",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой нарушение функционирования особо важных элементов коммунального хозяйства, объектов связи и телевидения",
                "correct": true
            },
            {
                "id": "450283",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой значительный материальный ущерб",
                "correct": true
            },
            {
                "id": "450286",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой опасность для жизни людей",
                "correct": true
            },
            {
                "id": "450287",
                "name": "электроприемники, перерыв электроснабжения которых приводит к массовому недоотпуску продукции, массовым простоям рабочих, механизмов и промышленного транспорта, нарушению нормальной деятельности значительного количества городских и сельских жителей",
                "correct": false
            },
            {
                "id": "450285",
                "name": "электроприемники, перерыв электроснабжения которых может повлечь за собой угрозу для безопасности государства",
                "correct": true
            }
        ]
    },
    {
        "id": "437368",
        "name": "Имеет ли право организация по проведению государственной экспертизы участвовать в осуществлении архитектурно-строительного проектирования и (или) инженерных изысканий и в какой форме?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437370",
                "name": "организация по проведению государственной экспертизы вправе участвовать в осуществлении архитектурно-строительного проектирования и (или) инженерных изысканий в качестве юридического лица, на которое возложена, в соответствии с договором, обязанность по техническому аудиту архитектурно-строительного проектирования и (или) инженерных изысканий",
                "correct": false
            },
            {
                "id": "437372",
                "name": "организация по проведению государственной экспертизы не вправе участвовать в осуществлении архитектурно-строительного проектирования ",
                "correct": true
            },
            {
                "id": "437374",
                "name": "организация по проведению государственной экспертизы не вправе участвовать в осуществлении инженерных изысканий",
                "correct": true
            },
            {
                "id": "437371",
                "name": "организация по проведению государственной экспертизы вправе участвовать в осуществлении архитектурно-строительного проектирования и (или) инженерных изысканий в случае наделения её такими полномочиями органом власти субъекта Российской Федерации",
                "correct": false
            },
            {
                "id": "437369",
                "name": "организация по проведению государственной экспертизы вправе участвовать в осуществлении архитектурно-строительного проектирования и (или) инженерных изысканий в качестве организации по надзору и контролю за осуществлением архитектурно-строительного проектирования и (или) инженерных изысканий",
                "correct": false
            },
            {
                "id": "437373",
                "name": "организация по проведению государственной экспертизы вправе участвовать в осуществлении архитектурно-строительного проектирования и (или) инженерных изысканий, в том числе, в случае, если подготовка проектной документации и результаты инженерных изысканий выполняются для объекта, строительство которого финансируется за счёт средств бюджетов бюджетной системы Российской Федерации",
                "correct": false
            }
        ]
    },
    {
        "id": "437548",
        "name": "При каких условиях раздел \"Смета на строительство, реконструкцию, капитальный ремонт, снос объекта капитального строительства\" разрабатывается в составе проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437554",
                "name": "когда капитальный ремонт финансируется с привлечением средств бюджетов бюджетной системы Российской Федерации, средств лиц, указанных в части 1 статьи 8.3 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "437549",
                "name": "всегда, независимо от условий финансирования",
                "correct": false
            },
            {
                "id": "437553",
                "name": "только если объект является линейным",
                "correct": false
            },
            {
                "id": "437552",
                "name": "только если объект является нежилым или промышленным",
                "correct": false
            },
            {
                "id": "437550",
                "name": "когда строительство, реконструкция, снос финансируются с привлечением средств бюджетов бюджетной системы Российской Федерации, средств юридических лиц, указанных в части 2 статьи 8.3 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "437551",
                "name": "только при условии финансирования строительства исключительно за счет собственных (внебюджетных) средств",
                "correct": false
            }
        ]
    },
    {
        "id": "438025",
        "name": "Кем обеспечивается формирование и ведение информационной модели объекта капитального строительства в случаях, установленных постановлением Правительства Российской Федерации от 5 марта 2021 г. № 331 \"Об установлении случаев, при которых застройщиком, техническим заказчиком, лицом, обеспечивающим или осуществляющим подготовку обоснования инвестиций, и (или) лицом, ответственным за эксплуатацию объекта капитального строительства, обеспечиваются формирование и ведение информационной модели объекта капитального строительства\"?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438029",
                "name": "лицом, ответственным за эксплуатацию объекта капитального строительства",
                "correct": true
            },
            {
                "id": "438028",
                "name": "лицом, обеспечившим или осуществляющим подготовку обоснования инвестиций",
                "correct": true
            },
            {
                "id": "438030",
                "name": "лицом, обеспечивающим реализацию решения о комплексном развитии территории",
                "correct": false
            },
            {
                "id": "438027",
                "name": "техническим заказчиком",
                "correct": true
            },
            {
                "id": "438026",
                "name": "застройщиком",
                "correct": true
            },
            {
                "id": "438031",
                "name": "лицом, аттестованным на право подготовки заключений экспертизы проектной документации",
                "correct": false
            }
        ]
    },
    {
        "id": "437906",
        "name": "Какие мероприятия должна содержать проектная документация уникальных объектов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437907",
                "name": "перечень мероприятий по гражданской обороне",
                "correct": true
            },
            {
                "id": "437910",
                "name": "перечень мероприятий по предупреждению чрезвычайных ситуаций природного и техногенного характера",
                "correct": true
            },
            {
                "id": "437908",
                "name": "перечень мероприятий по противодействию терроризму",
                "correct": true
            },
            {
                "id": "437911",
                "name": "технико-экономическое обоснование",
                "correct": false
            },
            {
                "id": "437912",
                "name": "перечень мероприятий по рекультивации земель",
                "correct": false
            },
            {
                "id": "437909",
                "name": "перечень мероприятий по недопущению сверхнормативных осадок",
                "correct": false
            }
        ]
    },
    {
        "id": "437486",
        "name": "Какие сведения включаются в подраздел единого государственного реестра заключений экспертизы проектной документации объектов капитального строительства, касающийся сведений о представленной для проведения экспертизы проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437491",
                "name": "сметная стоимость строительства, реконструкции, капитального ремонта, сноса, работ по сохранению объектов культурного наследия (памятников истории и культуры) народов Российской Федерации (на дату начала проведения экспертизы и на дату утверждения заключения экспертизы) и сведения о проверке достоверности ее определения (в случае, если сметная стоимость в соответствии с законодательством Российской Федерации подлежит такой проверке)",
                "correct": true
            },
            {
                "id": "437489",
                "name": "функциональное назначение объекта капитального строительства, применительно к которому подготовлена проектная документация, и его основные проектируемые технико-экономические показатели (в соответствии с проектной документацией)",
                "correct": true
            },
            {
                "id": "437490",
                "name": "наименование и адрес (местоположение) объекта капитального строительства, применительно к которому подготовлена проектная документация (в отношении проектной документации неоднократного применения указанные сведения не включаются)",
                "correct": true
            },
            {
                "id": "437492",
                "name": "сведения о застройщике (техническом заказчике), обеспечившем подготовку проектной документации",
                "correct": true
            },
            {
                "id": "437488",
                "name": "сведения о природных и иных условиях территории, на которой планируется осуществлять строительство (климатический район и подрайон, ветровой район, снеговой район, интенсивность сейсмических воздействий, инженерно-геологические условия)",
                "correct": true
            },
            {
                "id": "437487",
                "name": "сведения о документации по планировке территории, на основании которых была осуществлена подготовка проектной документации",
                "correct": false
            }
        ]
    },
    {
        "id": "438072",
        "name": "В отношении какой документации проведение экспертизы проектной документации не предусмотрено?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438073",
                "name": "изменения, внесенные в проектную документацию после получения заключения органа государственного строительного надзора о соответствии построенного, реконструированного объекта капитального строительства требованиям проектной документации",
                "correct": true
            },
            {
                "id": "438076",
                "name": "раздел проектной документации «Смета на текущий ремонт объекта капитального строительства",
                "correct": true
            },
            {
                "id": "438074",
                "name": "проект рекультивации земель если в рамках рекультивации не предусмотрено строительство, реконструкция объекта капитального строительства",
                "correct": true
            },
            {
                "id": "438075",
                "name": "проектная документация, предусматривающая строительство легковозводимых конструкций (некапитальных объектов)",
                "correct": true
            },
            {
                "id": "438077",
                "name": "раздел проектной документации «Смета на капитальный ремонт объекта капитального строительства»",
                "correct": false
            }
        ]
    },
    {
        "id": "437887",
        "name": "Кем определяется состав разделов проектной документации при проведении капитального ремонта объектов капитального строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437891",
                "name": "лицом, осуществляющим подготовку проектной документации",
                "correct": false
            },
            {
                "id": "437893",
                "name": "региональным оператором",
                "correct": false
            },
            {
                "id": "437890",
                "name": "лицом, осуществляющим эксплуатацию объекта капитального строительства",
                "correct": false
            },
            {
                "id": "437888",
                "name": "застройщиком",
                "correct": true
            },
            {
                "id": "437892",
                "name": "лицом, осуществляющего строительство",
                "correct": false
            },
            {
                "id": "437889",
                "name": "техническим заказчиком",
                "correct": true
            }
        ]
    },
    {
        "id": "450151",
        "name": "Какие электроприемники жилых и общественных зданий относятся к электроприемникам III категории по надежности электроснабжения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450155",
                "name": "здания учреждений управления, проектных и конструкторских организаций, научно-исследовательских институтов с числом работающих до 50 человек",
                "correct": true
            },
            {
                "id": "450156",
                "name": "комплекс электроприемников салонов-парикмахерских с числом рабочих мест свыше 15, ателье и комбинатов бытового обслуживания с числом рабочих мест свыше 50, прачечных и химчисток производительностью свыше 500 кг белья в смену, бань с числом мест свыше 100",
                "correct": false
            },
            {
                "id": "450153",
                "name": "электроприемники операционных и родильных блоков, отделений анестезиологии, реанимации и интенсивной терапии, кабинетов лапароскопии, бронхоскопии и ангиографии, противопожарных устройств и охранной сигнализации, эвакуационного освещения и больничных лифтов",
                "correct": false
            },
            {
                "id": "450154",
                "name": "здания учреждений управления, проектных и конструкторских организаций, научно-исследовательских институтов с числом работающих свыше 50 человек, а также здания областного, городского и районного значения до 50 человек",
                "correct": false
            },
            {
                "id": "450157",
                "name": "зарядные станции и пункты зарядки для электромобилей",
                "correct": true
            },
            {
                "id": "450152",
                "name": "отдельно стоящие и встроенные центральные тепловые пункты (ЦТП), индивидуальные тепловые пункты (ИТП) многоквартирных жилых домов",
                "correct": false
            }
        ]
    },
    {
        "id": "450132",
        "name": "Какая предусматривается средняя освещенность открытых стоянок автомобилей на улицах всех категорий?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450137",
                "name": "не менее 2 лк",
                "correct": false
            },
            {
                "id": "450133",
                "name": "не менее 15 лк",
                "correct": false
            },
            {
                "id": "450134",
                "name": "не менее 10 лк",
                "correct": false
            },
            {
                "id": "450136",
                "name": "не менее 4 лк",
                "correct": false
            },
            {
                "id": "450135",
                "name": "не менее 6 лк",
                "correct": true
            }
        ]
    },
    {
        "id": "450075",
        "name": "Для каких электроприемников не допускается установка УДТ в общественных зданиях и т.п.?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450077",
                "name": "системы обеспечения безопасности зданий",
                "correct": true
            },
            {
                "id": "450078",
                "name": "системы поддержания жизнедеятельности больных",
                "correct": true
            },
            {
                "id": "450076",
                "name": "системы противопожарной защиты",
                "correct": true
            },
            {
                "id": "450080",
                "name": "система рабочего освещения",
                "correct": false
            },
            {
                "id": "450081",
                "name": "архитектурная подсветка зданий",
                "correct": false
            },
            {
                "id": "450079",
                "name": "насосы системы канализации",
                "correct": false
            }
        ]
    },
    {
        "id": "437853",
        "name": "Для каких объектов в состав проектной документации включается раздел «мероприятия по обеспечению доступа инвалидов к объекту капитального строительства»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437854",
                "name": "объектов транспорта",
                "correct": true
            },
            {
                "id": "437857",
                "name": "объектов образования",
                "correct": true
            },
            {
                "id": "437855",
                "name": "объектов религиозного назначения",
                "correct": true
            },
            {
                "id": "437858",
                "name": "объектов культуры",
                "correct": true
            },
            {
                "id": "437859",
                "name": "объектов делового назначения",
                "correct": true
            },
            {
                "id": "437856",
                "name": "объектов жилищного фонда",
                "correct": true
            }
        ]
    },
    {
        "id": "437514",
        "name": "Какие сведения содержит раздел проектной документации \"Проект организации строительства\" (на объекты капитального строительства производственного\nи непроизводственного назначения)?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437516",
                "name": "о потребности объекта капитального строительства в топливе, газе, воде и электрической энергии",
                "correct": false
            },
            {
                "id": "437519",
                "name": "поэтажные планы зданий и сооружений с указанием размеров и экспликации помещений",
                "correct": false
            },
            {
                "id": "437515",
                "name": "описание транспортной инфраструктуры",
                "correct": true
            },
            {
                "id": "437518",
                "name": "характеристику земельного участка, предоставленного для размещения объекта капитального строительства",
                "correct": false
            },
            {
                "id": "437517",
                "name": "предложения по организации службы геодезического и лабораторного контроля",
                "correct": true
            }
        ]
    },
    {
        "id": "450327",
        "name": "Для каких потребителей жилых и общественных зданий требуется компенсация реактивной мощности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450329",
                "name": "для местных и центральных тепловых пунктов, насосных, котельных и других потребителей, предназначенных для обслуживания жилых и общественных зданий, если в нормальном режиме работы расчетная мощность компенсирующего устройства на каждом рабочем вводе составляет 55 кВАр",
                "correct": true
            },
            {
                "id": "450330",
                "name": "для местных и центральных тепловых пунктов, насосных, котельных и других потребителей, предназначенных для обслуживания жилых и общественных зданий, если в нормальном режиме работы расчетная мощность компенсирующего устройства на каждом рабочем вводе составляет 75 кВАр",
                "correct": true
            },
            {
                "id": "450328",
                "name": "для местных и центральных тепловых пунктов, насосных, котельных и других потребителей, предназначенных для обслуживания жилых и общественных зданий, если в нормальном режиме работы расчетная мощность компенсирующего устройства на каждом рабочем вводе составляет 25 кВАр",
                "correct": false
            },
            {
                "id": "450331",
                "name": "для местных и центральных тепловых пунктов, насосных, котельных и других потребителей, предназначенных для обслуживания жилых и общественных зданий, если в нормальном режиме работы расчетная мощность компенсирующего устройства на каждом рабочем вводе составляет 10 кВАр",
                "correct": false
            }
        ]
    },
    {
        "id": "437417",
        "name": "Что из перечисленного входит в предмет государственной экспертизы проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437421",
                "name": "оценка соответствия проектной документации требованиям к обеспечению надежности и безопасности электроэнергетических систем и объектов электроэнергетики",
                "correct": true
            },
            {
                "id": "437420",
                "name": "оценка соответствия проектной документации требованиям, установленным в разрешении на отклонение от предельных параметров разрешенного строительства",
                "correct": false
            },
            {
                "id": "437418",
                "name": "оценка соответствия проектной документации требованиям в области охраны окружающей среды",
                "correct": true
            },
            {
                "id": "437419",
                "name": "проверка достоверности определения сметной стоимости в случаях, установленных частью 2 статьи 8.3 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "437422",
                "name": "оценка соответствия проектной документации требованиям безопасности труда",
                "correct": false
            },
            {
                "id": "437423",
                "name": "оценка соответствия проектной документации результатам инженерных изысканий",
                "correct": true
            }
        ]
    },
    {
        "id": "437797",
        "name": "На основании каких документов осуществляется подготовка проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437801",
                "name": "проект межевания территории для линейного объекта",
                "correct": true
            },
            {
                "id": "437798",
                "name": "информации, указанной в градостроительном плане земельного участка для нелинейного объекта",
                "correct": true
            },
            {
                "id": "437802",
                "name": "кадастровый план земельного участка",
                "correct": false
            },
            {
                "id": "437800",
                "name": "проект планировки территории для линейного объекта",
                "correct": true
            },
            {
                "id": "437799",
                "name": "результаты инженерных изысканий ",
                "correct": true
            },
            {
                "id": "437803",
                "name": "технические условия подключения (технологического присоединения)",
                "correct": true
            }
        ]
    },
    {
        "id": "450249",
        "name": "Чему равна удельная расчётная нагрузка электроприёмников квартир жилых зданий с плитами на природном газе при количестве квартир 1-5 в соответствии с СП 256.1325800.2016?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450252",
                "name": "4,5 кВт",
                "correct": true
            },
            {
                "id": "450254",
                "name": "10 кВт",
                "correct": false
            },
            {
                "id": "450255",
                "name": "12 кВт",
                "correct": false
            },
            {
                "id": "450251",
                "name": "2,8 кВт",
                "correct": false
            },
            {
                "id": "450250",
                "name": "2,3 кВт",
                "correct": false
            },
            {
                "id": "450253",
                "name": "6 кВт",
                "correct": false
            }
        ]
    },
    {
        "id": "450634",
        "name": "Каковы требования при пересечении и сближении ВЛ с контактными проводами и несущими тросами трамвайных и троллейбусных линий в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450636",
                "name": "в зоне, занятой сооружениями контактной сети, включая опоры, опоры ВЛ должны быть анкерного типа",
                "correct": true
            },
            {
                "id": "450640",
                "name": "расстояние от проводов ВЛ при наибольшей стреле провесе должно быть не менее 8 м от головки рельса трамвайной линии и 10,5 м до проезжей части в зоне троллейбусной линии",
                "correct": true
            },
            {
                "id": "450639",
                "name": "сечение алюминиевых многопроволочных проводов ВЛ должно быть не менее 35 мм², сталеалюминиевых – не менее 25 мм², несущая жила СИП – 25 мм², сечение жилы СИП со всеми несущими проводниками жгута – не менее 16 мм²",
                "correct": false
            },
            {
                "id": "450638",
                "name": "сечение алюминиевых многопроволочных проводов ВЛ должно быть не менее 35 мм², сталеалюминиевых – не менее 25 мм², несущая жила СИП – 35 мм², сечение жилы СИП со всеми несущими проводниками жгута – не менее 25 мм²",
                "correct": false
            },
            {
                "id": "450637",
                "name": "провода ВЛ должны располагаться над несущими тросами контактных проводов",
                "correct": true
            },
            {
                "id": "450635",
                "name": "в зоне, занятой сооружениями контактной сети, включая опоры, прокладка ВЛ запрещается",
                "correct": false
            }
        ]
    },
    {
        "id": "437713",
        "name": "Перечислите документы, в результате применения которых обеспечивается соблюдение требований Федерального закона от 30 декабря 2009 г. № 384-ФЗ \"Технический регламент о безопасности зданий и сооружений\"",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437715",
                "name": "стандарты иностранных государств и своды правил иностранных государств\n\n",
                "correct": true
            },
            {
                "id": "437718",
                "name": "отраслевые стандарты государственных компаний",
                "correct": false
            },
            {
                "id": "437714",
                "name": "национальные стандарты Российской Федерации и (или) своды правил (часть национального стандарта и (или) часть свода правил)",
                "correct": true
            },
            {
                "id": "437719",
                "name": "стандарты организаций",
                "correct": true
            },
            {
                "id": "437717",
                "name": "региональные стандарты и региональные своды правил",
                "correct": true
            },
            {
                "id": "437716",
                "name": "международные стандарты",
                "correct": true
            }
        ]
    },
    {
        "id": "437832",
        "name": "Какие разделы включаются в обязательном порядке в состав проектной документации для строительства объектов капитального строительства коммунально-бытового назначения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437834",
                "name": "декларация пожарной безопасности",
                "correct": false
            },
            {
                "id": "437833",
                "name": "схема планировочной организации земельного участка",
                "correct": true
            },
            {
                "id": "437838",
                "name": "объемно-планировочные и архитектурные решения",
                "correct": true
            },
            {
                "id": "437836",
                "name": "требования к обеспечению безопасной эксплуатации объектов капитального строительства",
                "correct": true
            },
            {
                "id": "437837",
                "name": "генеральный план",
                "correct": false
            },
            {
                "id": "437835",
                "name": "проект организации строительства объектов капитального строительства",
                "correct": true
            }
        ]
    },
    {
        "id": "437583",
        "name": "Какие сведения включаются в обязательном порядке в раздел 1 \"Пояснительная записка\" проектной документации для строительства линейных объектов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437589",
                "name": "описание решений по организации рельефа трассы и инженерной подготовке территории",
                "correct": false
            },
            {
                "id": "437587",
                "name": "описание маршрутов прохождения линейного объекта по территории района строительства, реконструкции, капитального ремонта",
                "correct": true
            },
            {
                "id": "437588",
                "name": "сведения о линейном объекте с указанием наименования, назначения и месторасположения начального и конечного пунктов линейного объекта",
                "correct": true
            },
            {
                "id": "437586",
                "name": "сведения о климатической, географической и инженерно-геологической характеристике района, на территории которого предполагается осуществлять строительство реконструкцию, капитальный ремонт линейного объекта",
                "correct": true
            },
            {
                "id": "437585",
                "name": "характеристика трассы линейного объекта",
                "correct": false
            },
            {
                "id": "437584",
                "name": "сведения о наличии зон с особыми условиями использования территорий, расположенных в границах земельного участка, предназначенного для размещения объекта капитального строительства",
                "correct": false
            }
        ]
    },
    {
        "id": "450586",
        "name": "Из какого материала может быть выполнена главная заземляющая шина, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450590",
                "name": "латунная",
                "correct": false
            },
            {
                "id": "450588",
                "name": "алюминиевая",
                "correct": false
            },
            {
                "id": "450587",
                "name": "стальная",
                "correct": true
            },
            {
                "id": "450589",
                "name": "медная",
                "correct": true
            },
            {
                "id": "450591",
                "name": "из любого металла",
                "correct": false
            }
        ]
    },
    {
        "id": "437846",
        "name": "Какие разделы включаются в обязательном порядке в состав проектной документации для строительства линий электропередач?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437850",
                "name": "схема планировочной организации земельного участка",
                "correct": false
            },
            {
                "id": "437849",
                "name": "здания, строения и сооружения, входящие в инфраструктуру линейного объекта",
                "correct": true
            },
            {
                "id": "437852",
                "name": "мероприятия по обеспечению пожарной безопасности",
                "correct": true
            },
            {
                "id": "437848",
                "name": "иная документация в случаях, предусмотренных законодательными и иными нормативными правовыми актами Российской Федерации ",
                "correct": true
            },
            {
                "id": "437847",
                "name": "мероприятия по охране окружающей среды",
                "correct": true
            },
            {
                "id": "437851",
                "name": "декларация пожарной безопасности",
                "correct": false
            }
        ]
    },
    {
        "id": "450544",
        "name": "Электросетевые объекты какого класса напряжения относятся к особо опасным и технически сложным объектам, в соответствии с Градостроительным кодексом Российской Федерации? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450548",
                "name": "линии электропередачи и иные объекты электросетевого хозяйства напряжением 220 киловольт и более",
                "correct": false
            },
            {
                "id": "450549",
                "name": "линии электропередачи и иные объекты электросетевого хозяйства напряжением 110 киловольт и более",
                "correct": false
            },
            {
                "id": "450545",
                "name": "линии электропередачи и иные объекты электросетевого хозяйства напряжением 330 киловольт и более",
                "correct": true
            },
            {
                "id": "450550",
                "name": "линии электропередачи и иные объекты электросетевого хозяйства напряжением 750 киловольт и более",
                "correct": false
            },
            {
                "id": "450546",
                "name": "линии электропередачи напряжением 220 киловольт и более",
                "correct": false
            },
            {
                "id": "450547",
                "name": "линии электропередачи напряжением 330 киловольт и более",
                "correct": true
            }
        ]
    },
    {
        "id": "450197",
        "name": "Что понимается под термином «прямое прикосновение»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450198",
                "name": "электрический контакт людей с токоведущими частями, находящимися под напряжением",
                "correct": false
            },
            {
                "id": "450200",
                "name": "электрический контакт людей или животных с токоведущими частями, не находящимися под напряжением",
                "correct": false
            },
            {
                "id": "450201",
                "name": "электрический контакт людей с токоведущими частями, не находящимися под напряжением",
                "correct": false
            },
            {
                "id": "450199",
                "name": "электрический контакт людей или животных с токоведущими частями, находящимися под напряжением",
                "correct": true
            }
        ]
    },
    {
        "id": "450425",
        "name": "Что должно быть использовано для защиты от поражения электрическим током в нормальном режиме при прямом прикосновении, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450429",
                "name": "По отдельности или в сочетании следующие меры защиты от прямого прикосновения: основная изоляция токоведущих частей; ограждения и оболочки; установка барьеров; размещение вне зоны досягаемости; применение сверхнизкого (малого) напряжения. Для дополнительной защиты от прямого прикосновения в электроустановках напряжением до 1 кВ, при наличии требований других глав ПУЭ, следует применять устройства защитного отключения (УЗО) с номинальным отключающим дифференциальным током не более 30 мА",
                "correct": true
            },
            {
                "id": "450428",
                "name": "в электроустановках напряжением до 1 кВ, устройства защитного отключения (УЗО) с номинальным отключающим дифференциальным током не более 30 мА",
                "correct": false
            },
            {
                "id": "450427",
                "name": "двойная или усиленная изоляция; защитное электрическое разделение цепей; изолирующие (непроводящие) помещения, зоны, площадки",
                "correct": false
            },
            {
                "id": "450426",
                "name": "защитное заземление; автоматическое отключение питания; уравнивание потенциалов",
                "correct": false
            }
        ]
    },
    {
        "id": "450011",
        "name": "Какая система заземления должна быть для электроустановок жилых, общественных, административных и бытовых зданий?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450015",
                "name": "ТТ",
                "correct": false
            },
            {
                "id": "450013",
                "name": "TN-C-S",
                "correct": true
            },
            {
                "id": "450014",
                "name": "IT",
                "correct": false
            },
            {
                "id": "450012",
                "name": "TN-S",
                "correct": true
            },
            {
                "id": "450016",
                "name": "TN-C",
                "correct": false
            }
        ]
    },
    {
        "id": "437459",
        "name": "Проектная документация каких объектов капитального строительства не подлежит обязательной государственной экспертизе (в том числе в случае если сметная стоимость строительства, реконструкции, капитального ремонта таких объектов не подлежит проверке на предмет достоверности ее определения)?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437463",
                "name": "отдельно стоящих объектов капитального строительства с количеством этажей не более чем два, общая площадь которых составляет не более чем 1500 кв. м и которые не предназначены для проживания граждан и осуществления производственной деятельности, относящихся к объектам массового пребывания граждан",
                "correct": false
            },
            {
                "id": "437460",
                "name": "объектов индивидуального жилищного строительства",
                "correct": true
            },
            {
                "id": "437461",
                "name": "отдельно стоящих объектов капитального строительства с количеством этажей не более чем два, общая площадь которых составляет не более чем 1500 кв. м и которые не предназначены для проживания граждан и осуществления производственной деятельности, не являющихся особо опасными, технически сложными или уникальными объектами",
                "correct": true
            },
            {
                "id": "437464",
                "name": "объектов капитального строительства, для строительства или реконструкции которых не требуется получение разрешения на строительство",
                "correct": true
            },
            {
                "id": "437462",
                "name": "отдельно стоящих объектов капитального строительства с количеством этажей не более чем два, общая площадь которых составляет не более чем 1500 кв. м и которые не предназначены для проживания граждан и осуществления производственной деятельности, строительство которых планируется осуществлять в границах зон охранных трубопроводов",
                "correct": false
            }
        ]
    },
    {
        "id": "450441",
        "name": "Минимальная высота у ограждений неизолированных токоведущих частей в распределительных устройствах, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450445",
                "name": "1,3 м",
                "correct": false
            },
            {
                "id": "450442",
                "name": "1,5 м",
                "correct": false
            },
            {
                "id": "450444",
                "name": "2 м",
                "correct": false
            },
            {
                "id": "450443",
                "name": "1,7 м",
                "correct": true
            }
        ]
    },
    {
        "id": "437664",
        "name": "Какие объекты инфраструктуры железнодорожного транспорта общего пользования должны быть идентифицированы как объекты повышенного уровня ответственности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437666",
                "name": "сортировочные горки с объемом переработки более 3500 вагонов в сутки",
                "correct": true
            },
            {
                "id": "437667",
                "name": "железнодорожные вокзалы расчетной вместимостью свыше 900 пассажиров",
                "correct": true
            },
            {
                "id": "437669",
                "name": "тоннели длиной более 500 м",
                "correct": true
            },
            {
                "id": "437670",
                "name": "железнодорожные пути необщего пользования",
                "correct": false
            },
            {
                "id": "437665",
                "name": "железнодорожный подвижной состав",
                "correct": false
            },
            {
                "id": "437668",
                "name": "мостовые переходы с опорами высотой от 50 до 100 м",
                "correct": true
            }
        ]
    },
    {
        "id": "450055",
        "name": "Какие электроприемники по степени обеспечения надежности электроснабжения относятся к первой категории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450056",
                "name": "противопожарные устройства",
                "correct": true
            },
            {
                "id": "450059",
                "name": "здания с числом работающих свыше 2000 чел ",
                "correct": true
            },
            {
                "id": "450057",
                "name": "лифты",
                "correct": true
            },
            {
                "id": "450058",
                "name": "дома 1-8 квартирные с электроплитами",
                "correct": false
            }
        ]
    },
    {
        "id": "450108",
        "name": "Какое значение сопротивления заземляющего устройства, к которому присоединены нейтрали генератора или трансформатора в любое время года должно быть при линейном напряжении 660 В?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450112",
                "name": "не более 4 Ом",
                "correct": false
            },
            {
                "id": "450110",
                "name": "не более 15 Ом ",
                "correct": false
            },
            {
                "id": "450109",
                "name": "не более 30 Ом",
                "correct": false
            },
            {
                "id": "450113",
                "name": "не более 8 Ом",
                "correct": false
            },
            {
                "id": "450111",
                "name": "не более 2 Ом",
                "correct": true
            }
        ]
    },
    {
        "id": "438005",
        "name": "Какие признаки относятся к обязательным для идентификации зданий и сооружений в соответствии с Федеральным законом от 30.12.2009 № 384-ФЗ \"Технический регламент о безопасности зданий и сооружений\"?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438006",
                "name": "наименование",
                "correct": false
            },
            {
                "id": "438011",
                "name": "принадлежность к объектам транспортной инфраструктуры и к другим объектам, функционально-технологические особенности которых влияют на их безопасность",
                "correct": true
            },
            {
                "id": "438008",
                "name": "площадь",
                "correct": false
            },
            {
                "id": "438007",
                "name": "назначение",
                "correct": true
            },
            {
                "id": "438010",
                "name": "наличие помещений с постоянным пребыванием людей",
                "correct": true
            },
            {
                "id": "438009",
                "name": "принадлежность к опасным производственным объектам",
                "correct": true
            }
        ]
    },
    {
        "id": "450185",
        "name": "Какие существуют системы искусственного освещения помещений?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450189",
                "name": "аварийное",
                "correct": false
            },
            {
                "id": "450188",
                "name": "рабочее",
                "correct": false
            },
            {
                "id": "450191",
                "name": "эвакуационное",
                "correct": false
            },
            {
                "id": "450187",
                "name": "комбинированное",
                "correct": true
            },
            {
                "id": "450186",
                "name": "общее (равномерное и локализованное)",
                "correct": true
            },
            {
                "id": "450190",
                "name": "резервное",
                "correct": false
            }
        ]
    },
    {
        "id": "450696",
        "name": "На какие уровни взрывозащиты подразделяется электрооборудование в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450697",
                "name": "взрывоопасное оборудование",
                "correct": false
            },
            {
                "id": "450700",
                "name": "взрывобезопасное электрооборудование",
                "correct": true
            },
            {
                "id": "450699",
                "name": "электрооборудование повышенной надёжности против взрыва",
                "correct": true
            },
            {
                "id": "450698",
                "name": "электрооборудование пониженной надёжности против взрыва",
                "correct": false
            },
            {
                "id": "450701",
                "name": "особовзрывобезопасное электрооборудование",
                "correct": true
            }
        ]
    },
    {
        "id": "437527",
        "name": "На каком основании квалификационный аттестат эксперта может быть аннулирован?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437528",
                "name": "в случае изменения действующего законодательства, до прохождения экспертом переаттестации",
                "correct": false
            },
            {
                "id": "437532",
                "name": "в случае установления факта участия эксперта в экспертизе объекта, расположенного на территории субъекта Российской Федерации, в котором эксперт не зарегистрирован",
                "correct": false
            },
            {
                "id": "437531",
                "name": "в случае установления факта представления для прохождения аттестации документов, содержащих недостоверные сведения",
                "correct": true
            },
            {
                "id": "437533",
                "name": "в случае установление факта участия эксперта в экспертизе при наличии личной заинтересованности в ее результатах, в том числе если в подготовке проектной документации и (или) выполнении инженерных изысканий участвовали эксперт лично или его близкие родственники (родители, дети, усыновители, усыновленные, родные братья и родные сестры, дедушка, бабушка, внуки), супруг",
                "correct": true
            },
            {
                "id": "437530",
                "name": "в случае вступления в законную силу решения уполномоченных органов о привлечении лица, которому выдан квалификационный аттестат, к ответственности за правонарушения в сфере его профессиональной деятельности",
                "correct": true
            },
            {
                "id": "437529",
                "name": "неучастие эксперта в экспертной деятельности в течение трех последовательных календарных лет",
                "correct": false
            }
        ]
    },
    {
        "id": "450497",
        "name": "Что из перечисленного является системой электроснабжения, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450499",
                "name": "совокупность электроустановок, электрических станций и электрических сетей",
                "correct": false
            },
            {
                "id": "450498",
                "name": "совокупность электроустановок для передачи и распределения электрической энергии, состоящая из подстанций, распределительных устройств, токопроводов, воздушных и кабельных линий электропередачи, работающих на определенной территории",
                "correct": false
            },
            {
                "id": "450501",
                "name": "электроприемник или группа электроприемников, объединенных технологическим процессом и размещающихся на определенной территории",
                "correct": false
            },
            {
                "id": "450500",
                "name": "совокупность электроустановок, предназначенных для обеспечения потребителей электрической энергией",
                "correct": true
            }
        ]
    },
    {
        "id": "449983",
        "name": "Какие преимущественные области применения кабелей с типом исполнения нг(А F/R)-LS?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "449984",
                "name": "в траншее",
                "correct": false
            },
            {
                "id": "449988",
                "name": "в кабельных тоннелях",
                "correct": true
            },
            {
                "id": "449986",
                "name": "во внутренних электроустановках",
                "correct": true
            },
            {
                "id": "449987",
                "name": "закрытых кабельных эстакадах",
                "correct": true
            },
            {
                "id": "449985",
                "name": "открытых кабельных сооружениях",
                "correct": false
            },
            {
                "id": "449989",
                "name": "по фасадам зданий",
                "correct": false
            }
        ]
    },
    {
        "id": "450451",
        "name": "Искусственное освещение, в соответствии с СП 52.13330.2016, подразделяется на:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450455",
                "name": "рабочее и эвакуационное",
                "correct": false
            },
            {
                "id": "450453",
                "name": "рабочее, аварийное, охранное и дежурное",
                "correct": true
            },
            {
                "id": "450456",
                "name": "общее и местное",
                "correct": false
            },
            {
                "id": "450452",
                "name": "рабочее и аварийное",
                "correct": false
            },
            {
                "id": "450454",
                "name": "естественное и совмещенное",
                "correct": false
            },
            {
                "id": "450457",
                "name": "рабочее и комбинированное",
                "correct": false
            }
        ]
    },
    {
        "id": "438046",
        "name": "Какие из перечисленных ниже факторов влияют \nна дату, по состоянию на которую в рамках \nповторной экспертизы после выдачи \nположительного заключения, необходимо \nоценивать изменения, внесенные в проектную \nдокументацию и результаты инженерных\n изысканий? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438049",
                "name": "изменения, внесенные в проектную документацию и результаты инженерных изысканий, всегда должны оцениваться на соответствие требованиям, действующим по состоянию на дату выдачи первичного (предыдущего повторного) положительного заключения экспертизы",
                "correct": false
            },
            {
                "id": "438050",
                "name": "необходимо учитывать условия задания застройщика (технического заказчика) на проектирование в части внесения изменений в проектную документацию в соответствии с требованиями, вступившими в силу после выдачи положительного заключения государственной экспертизы",
                "correct": true
            },
            {
                "id": "438048",
                "name": "необходимо учитывать дату, по состоянию на которую оценивались проектная документация и результаты инженерных изысканий, при проведении первичной (предыдущей повторной) экспертизы, по результатам которой было выдано положительное заключение экспертизы",
                "correct": true
            },
            {
                "id": "438047",
                "name": "дата выдачи градостроительного плана земельного участка (утверждения проекта планировки территории), представленного в ходе проведения повторной экспертизы взамен ранее представленного градостроительного плана земельного участка (проекта планировки территории",
                "correct": false
            },
            {
                "id": "438051",
                "name": "необходимо учитывать условия задания застройщика (технического заказчика) на проектирование в части внесения изменений в результаты инженерных изысканий в соответствии с требованиями, вступившими в силу после выдачи положительного заключения государственной экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "437597",
        "name": "В результате применения каких документов обеспечивается соблюдение требований Федерального закона от 30.12.2009 № 384-ФЗ \"Технический регламент о безопасности зданий и сооружений\"",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437601",
                "name": "заключение государственной экспертизы",
                "correct": false
            },
            {
                "id": "437602",
                "name": "стандарты организаций",
                "correct": true
            },
            {
                "id": "437598",
                "name": "проектная документация",
                "correct": false
            },
            {
                "id": "437599",
                "name": "национальные стандарты Российской Федерации",
                "correct": true
            },
            {
                "id": "437600",
                "name": "региональные своды правил",
                "correct": true
            },
            {
                "id": "437603",
                "name": "часть свода правил",
                "correct": true
            }
        ]
    },
    {
        "id": "437410",
        "name": "Что из перечисленного входит в предмет государственной экспертизы проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437416",
                "name": "оценка соответствия проектной документации требованиям технических регламентов",
                "correct": true
            },
            {
                "id": "437414",
                "name": "оценка соответствия проектной документации требованиям профессиональных стандартов",
                "correct": false
            },
            {
                "id": "437412",
                "name": "оценка соответствия проектной документации требованиям государственной охраны объектов культурного наследия",
                "correct": true
            },
            {
                "id": "437415",
                "name": "проверка достоверности определения сметной стоимости в случаях, установленных частью 2 статьи 8.3 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "437413",
                "name": "оценка соответствия проектной документации требованиям охраны труда",
                "correct": false
            },
            {
                "id": "437411",
                "name": "оценка соответствия проектной документации требованиям промышленной безопасности",
                "correct": true
            }
        ]
    },
    {
        "id": "437658",
        "name": "Какие объекты инфраструктуры воздушного транспорта идентифицируются как объекты повышенного уровня ответственности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437660",
                "name": "перроны аэродромов с искусственным покрытием с длиной взлетно-посадочной полосы 1300 метров и более",
                "correct": true
            },
            {
                "id": "437663",
                "name": "стартовые диспетчерские пункты модульного (контейнерного) типа",
                "correct": false
            },
            {
                "id": "437662",
                "name": "командно-диспетчерские и стартовые диспетчерские пункты высотой более трех этажей",
                "correct": true
            },
            {
                "id": "437661",
                "name": "перроны аэродромов с искусственным покрытием с длиной взлетно-посадочной полосы 1000 метров и более",
                "correct": false
            },
            {
                "id": "437659",
                "name": "аэровокзалы (терминалы) пропускной способностью 100 пассажиров в час и более",
                "correct": true
            }
        ]
    },
    {
        "id": "437555",
        "name": "В каком формате могут представляться электронные документы, содержащие сводный сметный расчет стоимости строительства, для проведения государственной экспертизы проектной документации? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437557",
                "name": "DOCX",
                "correct": false
            },
            {
                "id": "437556",
                "name": "PDF",
                "correct": false
            },
            {
                "id": "437561",
                "name": "EXE",
                "correct": false
            },
            {
                "id": "437560",
                "name": "ODS",
                "correct": true
            },
            {
                "id": "437559",
                "name": "XLSX",
                "correct": true
            },
            {
                "id": "437558",
                "name": "CAD",
                "correct": false
            }
        ]
    },
    {
        "id": "438112",
        "name": "При проведении оценки соответствия изменений, внесенных в проектную документацию и (или) результаты инженерных изысканий после выдачи положительного заключения экспертизы, в рамках экспертного сопровождения:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438116",
                "name": "не подлежат оценке соответствия изменений, которые влекут изменение сметной стоимости строительства (реконструкции) объекта капитального строительства",
                "correct": false
            },
            {
                "id": "438117",
                "name": "может проводиться оценка соответствия любых изменений, внесенных в результаты инженерных изысканий и (или) в проектную документацию",
                "correct": true
            },
            {
                "id": "438114",
                "name": "осуществляется оценка соответствия изменений, внесенных в результаты инженерных изысканий, получившие положительное заключение экспертизы, требованиям технических регламентов, и (или) изменений, внесенных в проектную документацию, получившую положительное заключение государственной экспертизы, требованиям технических регламентов, санитарно-эпидемиологическим требованиям, требованиям в области охраны окружающей среды, требованиям государственной охраны объектов культурного наследия, требованиям к безопасному использованию атомной энергии, требованиям промышленной безопасности, требованиям к обеспечению надежности и безопасности электроэнергетических систем и объектов электроэнергетики, требованиям антитеррористической защищенности объекта, заданию застройщика или технического заказчика на проектирование, результатам инженерных изысканий, включая оценку совместимости изменений, внесенных в результаты инженерных изысканий, получившие положительное заключение государственной экспертизы, требованиям технических регламентов, и (или) изменений, внесенных в проектную документацию, с частью проектной документацией, в которую указанные изменения не вносились",
                "correct": true
            },
            {
                "id": "438115",
                "name": "может осуществляться оценка соответствия внесенных в проектную документацию изменений, соответствующих требованиям части 3.8 статьи 49 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "438113",
                "name": "может осуществляться оперативное внесение изменений в проектную документацию и (или) результаты инженерных изысканий в порядке, определённом договором об экспертном сопровождении",
                "correct": false
            }
        ]
    },
    {
        "id": "437741",
        "name": "В каких границах осуществляется архитектурно-строительное проектирование?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437747",
                "name": "в границах земельного участка, принадлежащего правообладателю, которому органы местного самоуправления передали такой земельный участок в случаях, установленных бюджетным законодательством Российской Федерации",
                "correct": true
            },
            {
                "id": "437744",
                "name": "в границах зоны размещения объектов капитального строительства, установленных документацией по планировке территории",
                "correct": false
            },
            {
                "id": "437745",
                "name": "в границах земельного участка, принадлежащего застройщику",
                "correct": true
            },
            {
                "id": "437746",
                "name": "в границах населенных пунктов",
                "correct": false
            },
            {
                "id": "437743",
                "name": "в границах элемента планировочной структуры, установленных документацией по планировке территории",
                "correct": false
            },
            {
                "id": "437742",
                "name": "в границах проектирования, установленных заданием на проектирование",
                "correct": false
            }
        ]
    },
    {
        "id": "450472",
        "name": "Какие типы систем заземления применяются для медицинских помещений и питающих цепей электромедицинского оборудования, систем для жизнеобеспечения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450477",
                "name": "IT",
                "correct": true
            },
            {
                "id": "450475",
                "name": "TN-C-S",
                "correct": false
            },
            {
                "id": "450476",
                "name": "TN-S",
                "correct": true
            },
            {
                "id": "450474",
                "name": "TT",
                "correct": false
            },
            {
                "id": "450473",
                "name": "TN-С",
                "correct": false
            }
        ]
    },
    {
        "id": "437811",
        "name": "Кто вправе обратиться к правообладателю сети инженерно-технического обеспечения для заключения договора о подключении (технологическом присоединении) объектов капитального строительства к сетям инженерно-технического обеспечения?\n",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437817",
                "name": "лицо, которому в предусмотренных земельным законодательством случаях выдано разрешение на использование земель или земельного участка, находящегося в государственной или муниципальной собственности, без предоставления земельного участка и установления сервитута, публичного сервитута, а также лицо, являющееся обладателем сервитута или публичного сервитута, которые установлены в соответствии с гражданским законодательством, земельным законодательством\n",
                "correct": true
            },
            {
                "id": "437814",
                "name": "лицо, ответственное за эксплуатацию здания, сооружения",
                "correct": false
            },
            {
                "id": "437812",
                "name": "правообладатель земельного участка и (или) объекта капитального строительства\n",
                "correct": true
            },
            {
                "id": "437815",
                "name": "любое лицо",
                "correct": false
            },
            {
                "id": "437816",
                "name": "лицо, с которым заключен договор о комплексном развитии территории, при наличии утвержденных в установленных порядке проекта планировки территории комплексного развития, комплексной схемы инженерного обеспечения территории комплексного развития, схемы расположения земельного участка или земельных участков на кадастровом плане территории, градостроительного плана земельного участка",
                "correct": true
            },
            {
                "id": "437813",
                "name": "региональный оператор",
                "correct": false
            }
        ]
    },
    {
        "id": "437424",
        "name": "Каков максимальный срок проведения государственной экспертизы (без учета возможности его продления)?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437428",
                "name": "срок проведения государственной экспертизы проектной документации или проектной документации и результатов инженерных изысканий не должен превышать 60 календарных дней",
                "correct": false
            },
            {
                "id": "437429",
                "name": "срок проведения государственной экспертизы проектной документации и результатов инженерных изысканий в отношении жилых объектов капитального строительства, в том числе со встроенно-пристроенными нежилыми помещениями, не относящиеся к уникальным объектам, не должен превышать 30 рабочих дней",
                "correct": false
            },
            {
                "id": "437430",
                "name": "срок проведения государственной экспертизы результатов инженерных изысканий, которые направлены на государственную экспертизу до направления на экспертизу проектной документации, в течении 30 рабочих дней",
                "correct": true
            },
            {
                "id": "437425",
                "name": "срок проведения государственной экспертизы не должен превышать 42 рабочих дня",
                "correct": true
            },
            {
                "id": "437426",
                "name": "срок проведения государственной экспертизы не должен превышать 100 рабочих дней",
                "correct": false
            },
            {
                "id": "437427",
                "name": "срок проведения государственной экспертизы проектной документации или проектной документации и результатов инженерных изысканий в отношении объектов капитального строительства, строительство, реконструкция и (или) капитальный ремонт которых будут осуществляться в особых экономических зонах не должен превышать 45 рабочих дней",
                "correct": false
            }
        ]
    },
    {
        "id": "450095",
        "name": "На какие виды подразделяется эвакуационное освещение в соответствии с СП 52.13330.2016?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450100",
                "name": "дежурное освещение",
                "correct": false
            },
            {
                "id": "450096",
                "name": "резервное освещение",
                "correct": false
            },
            {
                "id": "450097",
                "name": "освещение путей эвакуации",
                "correct": true
            },
            {
                "id": "450101",
                "name": "антипаническое эвакуационное освещение",
                "correct": true
            },
            {
                "id": "450099",
                "name": "освещение безопасности",
                "correct": false
            },
            {
                "id": "450098",
                "name": "освещение зон повышенной опасности",
                "correct": true
            }
        ]
    },
    {
        "id": "450382",
        "name": "Наименьшее сечение стального заземляющего проводника, присоединяющего заземлитель рабочего заземления к главной заземляющей шине в ЭУ напряжением до 1000 В, в соответствии с ПУЭ?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450386",
                "name": "4 мм²",
                "correct": false
            },
            {
                "id": "450384",
                "name": "16 мм²",
                "correct": false
            },
            {
                "id": "450385",
                "name": "10 мм²",
                "correct": false
            },
            {
                "id": "450383",
                "name": "75 мм²",
                "correct": true
            }
        ]
    },
    {
        "id": "450126",
        "name": "Для здания, имеющего несколько обособленных вводов, указать что может быть использовано в качестве проводника уравнивания потенциалов между главными заземляющими шинами:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450127",
                "name": "в качестве проводника уравнивания потенциалов используется проводник, сечение которого должно быть не менее половины сечения РЕ (РЕN) проводника той линии среди отходящих от щитов низкого напряжения подстанций, которая имеет наибольшее значение",
                "correct": true
            },
            {
                "id": "450131",
                "name": "в качестве проводника уравнивания потенциалов используются сторонние проводящие части, если они соответствуют требованиям к сопротивлению электрической цепи",
                "correct": false
            },
            {
                "id": "450128",
                "name": "в качестве проводника уравнивания потенциалов используется проводник, сечение которого должно быть менее половины сечения РЕ (РЕN) проводника той линии среди отходящих от щитов низкого напряжения подстанций, которая имеет наибольшее значение",
                "correct": false
            },
            {
                "id": "450129",
                "name": "в качестве проводника уравнивания потенциалов используются сторонние проводящие части, если они соответствуют требованиям к непрерывности электрической цепи",
                "correct": true
            },
            {
                "id": "450130",
                "name": "в качестве проводника уравнивания потенциалов используются сторонние проводящие части",
                "correct": false
            }
        ]
    },
    {
        "id": "450350",
        "name": "Выбор кабелей для прокладки по трассам с различными условиями охлаждения:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450352",
                "name": "допускается для кабельных линий до 10 кВ, за исключением подводных, применение кабелей разных сечений, но не более трех при условии, что длина наименьшего отрезка составляет не менее 20 м (см. также 2.3.70) ",
                "correct": true
            },
            {
                "id": "450351",
                "name": "для кабельных линий, прокладываемых по трассам с различными условиями охлаждения, сечения кабелей должны выбираться по участку трассы с худшими условиями охлаждения, если длина его составляет более 10 м",
                "correct": true
            },
            {
                "id": "450354",
                "name": "для кабельных линий, прокладываемых по трассам с различными условиями охлаждения, сечения кабелей должны выбираться по участку трассы с худшими условиями охлаждения, если длина его составляет более 20 м",
                "correct": false
            },
            {
                "id": "450355",
                "name": "допускается для кабельных линий до 10 кВ, за исключением подводных, применение кабелей разных сечений, но не более трех при условии, что длина наименьшего отрезка составляет не менее 10 м (см. также 2.3.70)",
                "correct": false
            },
            {
                "id": "450353",
                "name": "для кабельных линий, прокладываемых по трассам с различными условиями охлаждения, сечения кабелей должны выбираться по участку трассы с худшими условиями охлаждения, если длина его составляет более 50 м",
                "correct": false
            }
        ]
    },
    {
        "id": "450178",
        "name": "Какой тип нейтрали электросети может предусматриваться для работы электрических сетей напряжением 220 кВ? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450184",
                "name": "нейтральная нейтраль",
                "correct": false
            },
            {
                "id": "450183",
                "name": "заземленная через дугогасящий реактор",
                "correct": false
            },
            {
                "id": "450179",
                "name": "только глухозаземленная нейтраль",
                "correct": true
            },
            {
                "id": "450181",
                "name": "заземленная через резистор",
                "correct": false
            },
            {
                "id": "450182",
                "name": "изолированная нейтраль",
                "correct": false
            },
            {
                "id": "450180",
                "name": "глухозаземленная нейтраль или эффективно заземленная",
                "correct": false
            }
        ]
    },
    {
        "id": "437625",
        "name": "В какой форме осуществляется обязательная оценка соответствия зданий, сооружений, процессов, осуществляемых на всех этапах их жизненного цикла, за исключением эксплуатации зданий, сооружений",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437627",
                "name": "в форме текущего контроля",
                "correct": false
            },
            {
                "id": "437626",
                "name": "в форме авторского надзора",
                "correct": false
            },
            {
                "id": "437631",
                "name": "в форме эксплуатационного контроля",
                "correct": false
            },
            {
                "id": "437629",
                "name": "в форме государственного строительного надзора",
                "correct": true
            },
            {
                "id": "437630",
                "name": "в форме ввода объекта в эксплуатацию",
                "correct": true
            },
            {
                "id": "437628",
                "name": "в форме строительного контроля",
                "correct": true
            }
        ]
    },
    {
        "id": "437632",
        "name": "В каких целях осуществляется оценка соответствия зданий, сооружений, процессов, осуществляемых на всех этапах их жизненного цикла?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437634",
                "name": "получения разрешения на ввод объекта в эксплуатацию",
                "correct": false
            },
            {
                "id": "437635",
                "name": "удостоверения соответствия результатов инженерных изысканий требованиям Градостроительного кодекса Российской Федерации ",
                "correct": false
            },
            {
                "id": "437636",
                "name": "удостоверения соответствия результатов инженерных изысканий требованиям Федерального закона от 30.12.2009 № 384-ФЗ \"Технический регламент о безопасности зданий и сооружений\"  ",
                "correct": true
            },
            {
                "id": "437638",
                "name": "осуществление такой оценки законодательно не предусмотрено",
                "correct": false
            },
            {
                "id": "437633",
                "name": "получения разрешения на строительство",
                "correct": false
            },
            {
                "id": "437637",
                "name": "удостоверения соответствия характеристик здания или сооружения, строительство которых завершено, требованиям  Федерального закона от 30.12.2009 № 384-ФЗ \"Технический регламент о безопасности зданий и сооружений\" перед вводом здания или сооружения в эксплуатацию",
                "correct": true
            }
        ]
    },
    {
        "id": "437541",
        "name": "Какие разделы включаются в обязательном порядке в состав проектной документации для строительства объектов капитального строительства производственного и непроизводственного назначения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437542",
                "name": "раздел \"Сведения об инженерном оборудовании, о сетях и системах инженерно-технического обеспечения\"",
                "correct": true
            },
            {
                "id": "437545",
                "name": "раздел \"Проект полосы отвода\"",
                "correct": false
            },
            {
                "id": "437543",
                "name": "раздел \"Мероприятия по охране окружающей среды\"",
                "correct": true
            },
            {
                "id": "437547",
                "name": "раздел \"Схема планировочной организации земельного участка\"",
                "correct": true
            },
            {
                "id": "437546",
                "name": "раздел \"Мероприятия по обеспечению доступа инвалидов к объекту капитального строительства\"",
                "correct": true
            },
            {
                "id": "437544",
                "name": "раздел \"Система водоснабжения\"",
                "correct": false
            }
        ]
    },
    {
        "id": "437520",
        "name": "Каким из перечисленных требований должен\nсоответствовать эксперт для переаттестации на право\nподготовки заключений экспертизы проектной \nдокументации и (или) результатов инженерных \nизысканий?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437524",
                "name": "эксперт должен обладать необходимыми знаниями в области \nзаконодательства Российской Федерации о техническом регулировании\n(включая требования к обеспечению безопасной эксплуатации объектов капитального строительства)\nв части, касающейся соответственно выполнения инженерных изысканий в целях проектирования, строительства \nи эксплуатации этих объектов либо осуществления их проектирования, строительства и эксплуатации",
                "correct": true
            },
            {
                "id": "437525",
                "name": "эксперт не реже одного раза в 3 года проходит повышение квалификации в организации, ведущей образовательную деятельность, требования к которой установлены федеральным органом исполнительной власти, осуществляющим функции по выработке и реализации государственной политики и нормативно-правовому регулированию в сфере строительства, архитектуры, градостроительства",
                "correct": false
            },
            {
                "id": "437523",
                "name": "эксперт должен обладать необходимыми знаниями в области законодательства Российской Федерации о градостроительной деятельности",
                "correct": true
            },
            {
                "id": "437521",
                "name": "эксперт должен постоянно проживать в Российской Федерации",
                "correct": true
            },
            {
                "id": "437522",
                "name": "эксперт не должен иметь непогашенную или неснятую судимость за совершение умышленного преступления",
                "correct": true
            },
            {
                "id": "437526",
                "name": "эксперт должен иметь стаж работы на соответствующих должностях в органах либо организациях, проводящих экспертизу проектной документации и (или) результатов инженерных изысканий, не менее чем 3 года",
                "correct": false
            }
        ]
    },
    {
        "id": "450565",
        "name": "На какое время, в соответствии с ПУЭ, допускается отключить электроприемники II категории?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450568",
                "name": "до 1 суток ",
                "correct": false
            },
            {
                "id": "450571",
                "name": "до устранения повреждения",
                "correct": false
            },
            {
                "id": "450569",
                "name": "до 10 суток",
                "correct": false
            },
            {
                "id": "450566",
                "name": "на время автоматического восстановления питания",
                "correct": false
            },
            {
                "id": "450570",
                "name": "на время выезда оперативной бригады",
                "correct": true
            },
            {
                "id": "450567",
                "name": "до 12-ти часов ",
                "correct": false
            }
        ]
    },
    {
        "id": "437692",
        "name": "Что понимается под определениями «знак обращения на рынке» и «знак соответствия»?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437695",
                "name": "знак обращения на рынке - обозначение продукции, которая ранее не находилась в обращении на территории Российской Федерации либо которая ранее выпускалась в обращение и свойства или характеристики которой были впоследствии изменены",
                "correct": false
            },
            {
                "id": "437697",
                "name": "знак соответствия - обозначение, служащее для информирования приобретателей, в том числе потребителей, о соответствии объекта сертификации требованиям системы добровольной сертификации",
                "correct": true
            },
            {
                "id": "437698",
                "name": "знак соответствия - обозначение, удостоверяющие соответствие выпускаемой в обращение продукции требованиям технических регламентов",
                "correct": false
            },
            {
                "id": "437693",
                "name": "знак обращения на рынке - обозначение, служащее для информирования приобретателей, в том числе потребителей, о соответствии выпускаемой в обращение продукции требованиям технических регламентов",
                "correct": true
            },
            {
                "id": "437694",
                "name": "знак обращения на рынке - документальное удостоверение соответствия продукции или иных объектов, процессов проектирования (включая изыскания), производства, строительства, монтажа, наладки, эксплуатации, хранения, перевозки, реализации и утилизации, выполнения работ или оказания услуг требованиям технических регламентов, документам по стандартизации или условиям договоров",
                "correct": false
            },
            {
                "id": "437696",
                "name": "знак соответствия - документ, удостоверяющий соответствие объекта требованиям технических регламентов, документам по стандартизации или условиям договоров",
                "correct": false
            }
        ]
    },
    {
        "id": "437507",
        "name": "Какие сведения единого государственного реестра заключений экспертизы проектной документации объектов капитального строительства относятся к сведениям, доступ к которым обеспечивается всем заинтересованным лицам на бесплатной основе в информационно-телекоммуникационной сети \"Интернет\" в форме открытых данных?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437509",
                "name": "дата заключения экспертизы",
                "correct": true
            },
            {
                "id": "437508",
                "name": "номер заключения экспертизы",
                "correct": true
            },
            {
                "id": "437510",
                "name": "результат проведенной экспертизы (положительное или отрицательное заключение экспертизы)",
                "correct": true
            },
            {
                "id": "437512",
                "name": "сведения о застройщике (техническом заказчике), обеспечившем подготовку проектной документации, по результатам рассмотрения которой подготовлено заключение экспертизы",
                "correct": true
            },
            {
                "id": "437513",
                "name": "сведения об источнике финансирования",
                "correct": false
            },
            {
                "id": "437511",
                "name": "сведения об экспертной организации",
                "correct": true
            }
        ]
    },
    {
        "id": "437438",
        "name": "Что из перечисленного является результатом государственной экспертизы проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437441",
                "name": "заключение о соответствии (положительное заключение) или несоответствии (отрицательное заключение) проектной документации заданию на проектирование",
                "correct": true
            },
            {
                "id": "437442",
                "name": "заключение о соответствии (положительный заключение) или несоответствии (отрицательное заключение) проектной документации требованиям к размещению объекта капитального строительства",
                "correct": false
            },
            {
                "id": "437444",
                "name": "заключение о достоверности (положительное заключение) или недостоверности (отрицательное заключение) определения сметной стоимости строительства, реконструкции, капитального ремонта, сноса, работ по сохранению объектов культурного наследия (памятников истории и культуры) народов Российской Федерации",
                "correct": true
            },
            {
                "id": "437440",
                "name": "заключение о соответствии (положительное заключение) или несоответствии (отрицательное заключение) проектной документации результатам инженерных изысканий ",
                "correct": true
            },
            {
                "id": "437439",
                "name": "заключение о соответствии (положительное заключение) или несоответствии (отрицательное заключение) проектной документации требованиям технических регламентов",
                "correct": true
            },
            {
                "id": "437443",
                "name": "заключение о соответствии (положительное заключение) или несоответствии (отрицательное заключение) проектной документации требованиям к комплексному развитию территории",
                "correct": false
            }
        ]
    },
    {
        "id": "438065",
        "name": "Какие основания предусмотрены для аннулирования квалификационного аттестата на право подготовки заключений экспертизы проектной документации и (или) экспертизы результатов инженерных изысканий до истечения срока его действия?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438068",
                "name": "вступление в законную силу решения уполномоченных органов о привлечении лица, которому выдан квалификационный аттестат, к ответственности за правонарушения в сфере его профессиональной деятельности",
                "correct": true
            },
            {
                "id": "438070",
                "name": "привлечение лица, которому выдан квалификационный аттестат, к уголовной ответственности за совершение преступления против личности",
                "correct": false
            },
            {
                "id": "438071",
                "name": "аннулирование аккредитации на право проведения негосударственной экспертизы у организации по проведению негосударственной экспертизы, в которой трудоустроен эксперт",
                "correct": false
            },
            {
                "id": "438066",
                "name": "установление факта участия эксперта в экспертизе при наличии личной заинтересованности в ее результатах",
                "correct": true
            },
            {
                "id": "438067",
                "name": "установление факта представления для прохождения аттестации документов, содержащих недостоверные сведения",
                "correct": true
            },
            {
                "id": "438069",
                "name": "установление факта участия эксперта или его близких родственников, супруга в подготовке проектной документации и (или) выполнении инженерных изысканий, в отношении которых выдано заключение экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "450256",
        "name": "От чего зависит значение коэффициента спроса для расчета нагрузок рабочего освещения питающей сети и вводов общественных зданий? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450260",
                "name": "от установленной мощности аварийного освещения",
                "correct": false
            },
            {
                "id": "450261",
                "name": "от общей площади освещаемых помещений",
                "correct": false
            },
            {
                "id": "450259",
                "name": "от установленной мощности рабочего освещения",
                "correct": true
            },
            {
                "id": "450257",
                "name": "от количества и типа осветительных приборов",
                "correct": false
            },
            {
                "id": "450258",
                "name": "от вида предприятия (организации, учреждения) ",
                "correct": true
            }
        ]
    },
    {
        "id": "450485",
        "name": "Резервное освещение в медицинских организациях, в соответствии с СП 158.13330.2014, следует предусматривать: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450490",
                "name": "ремонтные мастерские, производственные помещения предприятий общественного питания, прачечные",
                "correct": false
            },
            {
                "id": "450487",
                "name": "в помещениях физиотерапии, душевых, залов грязелечения",
                "correct": false
            },
            {
                "id": "450486",
                "name": "для дежурного (ночного) освещения палат",
                "correct": false
            },
            {
                "id": "450489",
                "name": "в помещениях оперативной части, хранения ящиков выездных бригад",
                "correct": true
            },
            {
                "id": "450488",
                "name": "в операционных блоках, реанимационных, родовых отделениях",
                "correct": true
            }
        ]
    },
    {
        "id": "438137",
        "name": "Укажите особенности представления проектной документации (результатов инженерных изысканий) для проведения повторной экспертизы: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438139",
                "name": "для проведения повторной экспертизы может представляться только часть проектной документации (результатов инженерных изысканий), в которую внесены изменения, если ранее проектная документация (результаты инженерных изысканий) представлялись для проведения экспертизы в электронном виде в полном объеме",
                "correct": true
            },
            {
                "id": "438140",
                "name": "для проведения повторной экспертизы всегда представляется проектная документация (результаты инженерных изысканий) в полном объеме",
                "correct": false
            },
            {
                "id": "438141",
                "name": "в случае если ранее документы представлялись на экспертизу на бумажном носителе при проведении повторной экспертизы организация по проведению экспертизы в целях оценки совместимости внесенных изменений с проектной документацией, в отношении которой была ранее проведена экспертиза, вправе дополнительно истребовать от заявителя представление материалов проектной документации, в которые изменения не вносились",
                "correct": true
            },
            {
                "id": "438138",
                "name": "проектная документация и результаты инженерных изысканий представляются для проведения повторной экспертизы в полном объеме в случае, если после проведения первичной (предыдущей повторной) экспертизы в законодательство Российской Федерации внесены изменения, в соответствии с которыми экспертиза должна осуществляться иной организацией по проведению экспертизы",
                "correct": true
            },
            {
                "id": "438142",
                "name": "представление изменений, внесенных в результаты инженерных изысканий после выдачи положительного заключения экспертизы, для проведения экспертного сопровождения не предусмотрено",
                "correct": false
            },
            {
                "id": "438143",
                "name": "в рамках повторной экспертизы разделы проектной документации, в которые внесены изменения после выдачи положительного заключения экспертизы, подлежат оценке соответствия в полном объеме",
                "correct": false
            }
        ]
    },
    {
        "id": "450478",
        "name": "Какой тип исполнения кабельных изделий следует применять для цепей питания электроприемников операционного, реанимационного и наркозно-дыхательного оборудования, а также для других электроприемников, которые должны сохранять работоспособность в условиях пожара в соответствии с СП 158.13330.2014?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450479",
                "name": "нг(A)-FRLSLTx",
                "correct": true
            },
            {
                "id": "450482",
                "name": "нг(A)-HFLTx",
                "correct": false
            },
            {
                "id": "450480",
                "name": "нг(A)-FRHFLTx",
                "correct": true
            },
            {
                "id": "450484",
                "name": "нг(A)-FRHF",
                "correct": false
            },
            {
                "id": "450481",
                "name": "нг(A)-LSLTx",
                "correct": false
            },
            {
                "id": "450483",
                "name": "нг(A)-FRLS",
                "correct": false
            }
        ]
    },
    {
        "id": "450465",
        "name": "Какие электроприёмники медицинских организаций, в соответствии с СП 158.13330.2014, относятся к \"особой\" группе I категории класс 0,5?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450466",
                "name": "медицинское электрооборудование помещений группы 2, относящееся к системе обеспечения безопасности",
                "correct": false
            },
            {
                "id": "450471",
                "name": "оборудование для подачи медицинских газов",
                "correct": false
            },
            {
                "id": "450469",
                "name": "системы пожарной сигнализации",
                "correct": true
            },
            {
                "id": "450468",
                "name": "аварийное (резервное) освещение",
                "correct": false
            },
            {
                "id": "450470",
                "name": "лифты для передвижения пожарных подразделений",
                "correct": false
            },
            {
                "id": "450467",
                "name": "аварийное (эвакуационное) освещение",
                "correct": true
            }
        ]
    },
    {
        "id": "438098",
        "name": "Какое влияние оказывает на проведение экспертизы проектной документации и результатов инженерных изысканий наличие зоны с особыми условиями территории на участке планируемого размещения проектируемого объекта? ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438104",
                "name": "размещение проектируемого объекта в границах зоны с особыми условиями использования территорий не оказывает влияния на подготовку проектной документации и проведение оценки соответствия проектной документации",
                "correct": false
            },
            {
                "id": "438100",
                "name": "при проведении оценки соответствия проектной документации необходимо учитывать ограничения, связанные с наличием зоны с особыми условиями использования территории",
                "correct": true
            },
            {
                "id": "438102",
                "name": "в технический отчет по результатам инженерно-экологических изысканий, представляемый для проведения экспертизы, должны быть включены сведения о зоне с особыми условиями территории",
                "correct": true
            },
            {
                "id": "438103",
                "name": "сведения об ограничениях использования земельного участка и границах зон с особыми условиями использования территорий должны быть отражены в градостроительном плане земельного участка, представляемом для проведения экспертизы",
                "correct": true
            },
            {
                "id": "438099",
                "name": "размещение проектируемого объекта в границах зоны с особыми условиями использования территорий является основанием для проведения в отношении проектной документации и результатов инженерных изысканий такого объекта государственной экспертизы",
                "correct": false
            },
            {
                "id": "438101",
                "name": "в случае, если планируется строительство в охранной зоне особо охраняемой природной территории регионального или местного значения, оценка соответствия проектной документации требованиям в области охраны окружающей среды осуществляется в рамках государственной экологической экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "450040",
        "name": "С каким номинальным током срабатывания должны предусматриваться УДТ для защиты групповых линий, питающих бытовые розеточные сети в общественных зданиях, квартирах жилых домов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450044",
                "name": "до 300 мА",
                "correct": false
            },
            {
                "id": "450043",
                "name": "100 мА ",
                "correct": false
            },
            {
                "id": "450042",
                "name": "не менее 50 мА ",
                "correct": false
            },
            {
                "id": "450041",
                "name": "не более 30 мА ",
                "correct": true
            }
        ]
    },
    {
        "id": "450599",
        "name": "Сечение защитных проводников, не входящих в состав кабелей или проложенных не в общей оболочке (трубе, коробе, на одном лотке) с фазными проводниками, в соответствии с ПУЭ, должно быть не менее:",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450602",
                "name": "4 мм2 по меди – при отсутствии механической защиты",
                "correct": true
            },
            {
                "id": "450601",
                "name": "2,5 мм2 по меди – при наличии механической защиты",
                "correct": true
            },
            {
                "id": "450604",
                "name": "6 мм2 по алюминию – при наличии механической защиты",
                "correct": false
            },
            {
                "id": "450600",
                "name": "1,5 мм2 по меди – при наличии механической защиты",
                "correct": false
            },
            {
                "id": "450605",
                "name": "16 мм2 по алюминию – при отсутствии механической защиты",
                "correct": true
            },
            {
                "id": "450603",
                "name": "2,5 мм2 по алюминию – при наличии механической защиты",
                "correct": false
            }
        ]
    },
    {
        "id": "437639",
        "name": "Какие из перечисленных объектов относятся к опасным и технически сложным?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437641",
                "name": "объекты космической инфраструктуры",
                "correct": true
            },
            {
                "id": "437640",
                "name": "линии электропередачи и иные объекты электросетевого хозяйства напряжением 330 киловольт и более",
                "correct": true
            },
            {
                "id": "437645",
                "name": "тепловые электростанции мощностью 150 мегаватт и выше",
                "correct": true
            },
            {
                "id": "437642",
                "name": "сооружения связи, являющиеся особо опасными, технически сложными в соответствии с законодательством Российской Федерации в области связи",
                "correct": true
            },
            {
                "id": "437644",
                "name": "подвесные канатные дороги",
                "correct": true
            },
            {
                "id": "437643",
                "name": "объекты инфраструктуры морского порта, предназначенные для стоянок и обслуживания маломерных, спортивных парусных и прогулочных судов",
                "correct": false
            }
        ]
    },
    {
        "id": "437769",
        "name": "Признаки объекта индивидуального жилищного строительства?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437771",
                "name": "отдельно стоящее здание с количеством надземных этажей не более чем три",
                "correct": true
            },
            {
                "id": "437773",
                "name": "отдельно стоящее здание высотой не более двадцати метров",
                "correct": true
            },
            {
                "id": "437770",
                "name": "отдельно стоящий блокированный жилой дом с количеством этажей два, предназначенный для проживания не более двух семей",
                "correct": false
            },
            {
                "id": "437772",
                "name": "отдельно стоящий блокированный жилой дом с количеством этажей два и мансардным этажом, предназначенный для проживания не более двух семей",
                "correct": false
            },
            {
                "id": "437775",
                "name": "отдельно стоящее здание, которое состоит из комнат и помещений вспомогательного использования, предназначенных для удовлетворения гражданами бытовых и иных нужд, связанных с их проживанием в таком здании, и не предназначено для раздела на самостоятельные объекты недвижимости",
                "correct": true
            },
            {
                "id": "437774",
                "name": "отдельно стоящий жилой дом с количеством надземных этажей два, подвальным и мансардным этажами, предназначенный для проживания двух семей",
                "correct": false
            }
        ]
    },
    {
        "id": "438124",
        "name": "Изменения, внесенные в проектную документацию, в отношении которой было выдано положительное заключение государственной (негосударственной) экспертизы: ",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438129",
                "name": "могут быть утверждены при наличии указанного в части 3.9 статьи 49 Градостроительного кодекса Российской Федерации и предоставленного органом исполнительной власти или организацией, проводившими экспертизу данной проектной документации, в ходе экспертного сопровождения подтверждения соответствия вносимых в данную проектную документацию изменений требованиям, указанным в части 3.9 статьи 49 Градостроительного кодекса Российской Федерации",
                "correct": true
            },
            {
                "id": "438126",
                "name": "могут быть утверждены, в том числе после получения заключения органа государственного строительного надзора о соответствии построенного, реконструированного объекта капитального строительства требованиям проектной документации",
                "correct": false
            },
            {
                "id": "438128",
                "name": "могут быть утверждены при наличии подтверждения соответствия вносимых в проектную документацию изменений требованиям, указанным в части 3.8 статьи 49 Градостроительного кодекса Российской Федерации, предоставленного лицом, являющимся членом саморегулируемой организации, основанной на членстве лиц, осуществляющих подготовку проектной документации, утвержденного привлеченным этим лицом специалистом по организации архитектурно-строительного проектирования в должности главного инженера проекта",
                "correct": true
            },
            {
                "id": "438125",
                "name": "не подлежат утверждению",
                "correct": false
            },
            {
                "id": "438127",
                "name": "могут быть утверждены после получения положительного заключения повторной государственной (негосударственной) экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "437361",
        "name": "Что относится к обязанностям организации по проведению государственной экспертизы?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437363",
                "name": "разъяснять, в том числе на возмездной основе, по запросам заинтересованных лиц порядок проведения негосударственной экспертизы",
                "correct": false
            },
            {
                "id": "437364",
                "name": "принимать меры по обеспечению сохранности документов, представленных для проведения государственной экспертизы",
                "correct": true
            },
            {
                "id": "437367",
                "name": "оказывать, в том числе на возмездной основе, консультирование заявителя по вопросам подготовки проектной документации и (или) выполнения инженерных изысканий ",
                "correct": false
            },
            {
                "id": "437365",
                "name": "принимать меры по неразглашению проектных решений и иной конфиденциальной информации, которая стала известна этой организации в связи с проведением государственной экспертизы",
                "correct": true
            },
            {
                "id": "437366",
                "name": "информировать органы государственной власти Российской Федерации о выявлении в процессе проведения экспертизы существенных нарушений законодательства Российской Федерации",
                "correct": false
            },
            {
                "id": "437362",
                "name": "разъяснять бесплатно по запросам заинтересованных лиц порядок проведения государственной экспертизы",
                "correct": true
            }
        ]
    },
    {
        "id": "437867",
        "name": "В каких случаях в состав проектной документации включается раздел \"смета\"?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437872",
                "name": "проведение работ по перепланировке и переустройству помещений многоквартирных жилых домов, финансируемых с привлечением средств юридических лиц, созданных субъектами Российской Федерации",
                "correct": false
            },
            {
                "id": "437869",
                "name": "проведение работ по сохранению объектов культурного наследия, финансируемых с привлечением средств бюджетов бюджетной системы Российской Федерации",
                "correct": true
            },
            {
                "id": "437868",
                "name": "строительство и реконструкция объектов капитального строительства, финансируемых с привлечением средств бюджетов бюджетной системы Российской Федерации",
                "correct": true
            },
            {
                "id": "437870",
                "name": "проведение природоохранных мероприятий, финансируемых с привлечением средств юридических лиц, доля в уставных капиталах которых Российской Федерации более 50%",
                "correct": false
            },
            {
                "id": "437871",
                "name": "проведение работ по благоустройству территорий, финансируемых с привлечением средств бюджетов бюджетной системы Российской Федерации",
                "correct": false
            }
        ]
    },
    {
        "id": "437987",
        "name": "Какие документы (их копии) должны быть приложены к разделу \"Пояснительная записка\" проектной документации в полном объеме?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437989",
                "name": "документы, содержащие сведения о потребности объекта капитального строительства в топливе, газе, воде и электрической энергии",
                "correct": false
            },
            {
                "id": "437990",
                "name": "документы (копии документов, оформленные в установленном порядке), указанные в подпунктах \"б\" и \"ч\" пункта 10 Положения № 87 ",
                "correct": true
            },
            {
                "id": "437988",
                "name": "отчетная документация по результатам инженерных изысканий",
                "correct": true
            },
            {
                "id": "437992",
                "name": "документы о согласовании отступлений от положений технических условий",
                "correct": true
            },
            {
                "id": "437991",
                "name": "обоснование границ санитарно-защитных зон объектов капитального строительства",
                "correct": false
            }
        ]
    },
    {
        "id": "437734",
        "name": "Применительно к каким объектам осуществляется архитектурно-строительное проектирование?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437735",
                "name": "строящимся частям объектов капитального строительства",
                "correct": true
            },
            {
                "id": "437736",
                "name": "строящимся объектам капитального строительства",
                "correct": true
            },
            {
                "id": "437739",
                "name": "реконструируемым объектам капитального строительства",
                "correct": true
            },
            {
                "id": "437737",
                "name": "демонтируемым объектам капитального строительства",
                "correct": false
            },
            {
                "id": "437740",
                "name": "переустраиваемым объектам капитального строительства",
                "correct": false
            },
            {
                "id": "437738",
                "name": "проектируемым объектам капитального строительства",
                "correct": false
            }
        ]
    },
    {
        "id": "437651",
        "name": "Какие из перечисленных объектов капитального строительства, на которых размещены объекты использования атомной энергии должны быть идентифицированы как объекты повышенного уровня ответственности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437655",
                "name": "материалы, содержащие или способные воспроизвести делящиеся (расщепляющиеся) ядерные вещества",
                "correct": false
            },
            {
                "id": "437653",
                "name": "сооружения, комплексы, полигоны, установки и устройства с ядерными зарядами для использования в мирных целях",
                "correct": true
            },
            {
                "id": "437654",
                "name": "не относящиеся к ядерным установкам комплексы, установки, аппараты, оборудование и изделия, в которых содержатся радиоактивные вещества или генерируется ионизирующее излучение",
                "correct": false
            },
            {
                "id": "437652",
                "name": "космические и летательные аппараты с ядерными реакторами",
                "correct": false
            },
            {
                "id": "437657",
                "name": "транспортные и транспортабельные средства с ядерными реакторами",
                "correct": false
            },
            {
                "id": "437656",
                "name": "сооружения и комплексы с промышленными, экспериментальными и исследовательскими ядерными реакторами, критическими и подкритическими ядерными стендами",
                "correct": true
            }
        ]
    },
    {
        "id": "437776",
        "name": "В каких случаях при подготовке проектной документации не требуется членство в саморегулируемых организациях в области архитектурно-строительного проектирования?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437780",
                "name": "для коммерческих организаций, в уставных капиталах которых доля государственных и муниципальных унитарных предприятий, государственных и муниципальных автономных учреждений составляет пятьдесят и более процентов, в случае заключения такими коммерческими организациями договоров подряда на подготовку проектной документации с федеральными органами исполнительной власти",
                "correct": false
            },
            {
                "id": "437777",
                "name": "для коммерческих организаций, в уставных капиталах которых, доля юридических лиц, созданных публично-правовыми образованиями, составляет 50%, в случае заключения такими коммерческими организациями договоров подряда на подготовку проектной документации с  органами местного самоуправления ",
                "correct": false
            },
            {
                "id": "437781",
                "name": "для муниципальных унитарных предприятий в случае заключения ими договоров подряда на подготовку проектной документации органами самоуправления, в ведении которых находятся такие предприятия ",
                "correct": true
            },
            {
                "id": "437779",
                "name": "для коммерческих организаций, в уставных капиталах которых доля государственных и муниципальных унитарных предприятий, государственных и муниципальных автономных учреждений составляет более пятидесяти процентов, в случае заключения такими коммерческими организациями договоров подряда на подготовку проектной документации с федеральными органами исполнительной власти",
                "correct": true
            },
            {
                "id": "437782",
                "name": "если договор о подготовке проектной документации заключен с лицом, осуществляющим строительство",
                "correct": false
            },
            {
                "id": "437778",
                "name": "если договор о подготовке проектной документации заключен с региональным оператором",
                "correct": false
            }
        ]
    },
    {
        "id": "437569",
        "name": "Какие разделы включаются в обязательном порядке в состав проектной документации для строительства линейных объектов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437573",
                "name": "раздел \"Схема планировочной организации земельного участка\"",
                "correct": false
            },
            {
                "id": "437574",
                "name": "раздел \"Требования к обеспечению безопасной эксплуатации линейного объекта\"",
                "correct": true
            },
            {
                "id": "437571",
                "name": "раздел \"Здания, строения и сооружения, входящие в инфраструктуру линейного объекта\"",
                "correct": true
            },
            {
                "id": "437572",
                "name": "раздел \"Объемно-планировочные и архитектурные решения\"\n",
                "correct": false
            },
            {
                "id": "437575",
                "name": "раздел \"Система газоснабжения\"",
                "correct": false
            },
            {
                "id": "437570",
                "name": "раздел \"Пояснительная записка\"",
                "correct": true
            }
        ]
    },
    {
        "id": "437839",
        "name": "Какие разделы включаются в обязательном порядке в состав проектной документации для строительства объектов капитального строительства социально-культурного назначения?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437844",
                "name": "генеральный план",
                "correct": false
            },
            {
                "id": "437842",
                "name": "конструктивные решения",
                "correct": true
            },
            {
                "id": "437845",
                "name": "проект организации дорожного движения",
                "correct": false
            },
            {
                "id": "437843",
                "name": "сведения об инженерном оборудовании, о сетях и системах инженерно-технического обеспечения",
                "correct": true
            },
            {
                "id": "437841",
                "name": "декларация пожарной безопасности",
                "correct": false
            },
            {
                "id": "437840",
                "name": "пояснительная записка ",
                "correct": true
            }
        ]
    },
    {
        "id": "450065",
        "name": "Разрешается ли размещать ВРУ и ГРЩ на незадымляемых лестничных клетках?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": "797",
        "responses": [
            {
                "id": "450067",
                "name": "не разрешается",
                "correct": true
            },
            {
                "id": "450066",
                "name": "разрешается",
                "correct": false
            },
            {
                "id": "450068",
                "name": "разрешается со степенью защиты IP54",
                "correct": false
            },
            {
                "id": "450069",
                "name": "разрешается для потребителей третьей категории",
                "correct": false
            }
        ]
    },
    {
        "id": "437913",
        "name": "Кем утверждается проектная документация при наличии положительного заключения экспертизы проектной документации?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437917",
                "name": "лицом, осуществляющим подготовку проектной документации",
                "correct": false
            },
            {
                "id": "437919",
                "name": "региональным оператором",
                "correct": false
            },
            {
                "id": "437916",
                "name": "лицом, осуществляющим эксплуатацию объекта капитального строительства",
                "correct": false
            },
            {
                "id": "437914",
                "name": "застройщиком",
                "correct": true
            },
            {
                "id": "437918",
                "name": "лицом, осуществляющим строительство",
                "correct": false
            },
            {
                "id": "437915",
                "name": "техническим заказчиком",
                "correct": true
            }
        ]
    },
    {
        "id": "437927",
        "name": "Какие федеральные органы исполнительной власти вправе уточнять отдельные требования к содержанию разделов в отношении проектной документации на объекты военной инфраструктуры и объекты безопасности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437930",
                "name": "Федеральное агентство правительственной связи и информации при Президенте Российской Федерации",
                "correct": false
            },
            {
                "id": "437929",
                "name": "Министерство по делам гражданской обороны, чрезвычайным ситуациям и ликвидации последствий стихийных бедствий",
                "correct": false
            },
            {
                "id": "437932",
                "name": "Федеральная служба по экологическому, технологическому и атомному надзору",
                "correct": false
            },
            {
                "id": "437931",
                "name": "Федеральная служба безопасности Российской Федерации",
                "correct": true
            },
            {
                "id": "437928",
                "name": "Федеральная служба исполнения наказаний",
                "correct": false
            },
            {
                "id": "437933",
                "name": "Министерство обороны Российской Федерации",
                "correct": true
            }
        ]
    },
    {
        "id": "437762",
        "name": "В каких случаях осуществление подготовки проектной документации не требуется?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437768",
                "name": "при реконструкции садового дома",
                "correct": true
            },
            {
                "id": "437763",
                "name": "при строительстве садового дома",
                "correct": true
            },
            {
                "id": "437766",
                "name": "при строительстве объектов индивидуального жилищного строительства",
                "correct": true
            },
            {
                "id": "437767",
                "name": "при техническом перевооружении в составе реконструкции производственного объекта",
                "correct": false
            },
            {
                "id": "437765",
                "name": "при капитальном ремонте",
                "correct": false
            },
            {
                "id": "437764",
                "name": "при реконструкции объектов индивидуального жилищного строительства",
                "correct": true
            }
        ]
    },
    {
        "id": "437646",
        "name": "Какие из перечисленных объектов использования атомной энергии должны быть идентифицированы как объекты повышенного уровня ответственности?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437647",
                "name": "атомные станции",
                "correct": true
            },
            {
                "id": "437649",
                "name": "хранилища радиоактивных отходов",
                "correct": true
            },
            {
                "id": "437650",
                "name": "пункты хранения ядерных материалов и радиоактивных веществ",
                "correct": true
            },
            {
                "id": "437648",
                "name": "сооружения и комплексы с исследовательскими ядерными реакторами",
                "correct": true
            }
        ]
    },
    {
        "id": "438032",
        "name": "Какими способами допускается обосновывать соответствие архитектурных, функционально-технологических, конструктивных, инженерно-технических и иных решений и мероприятий, содержащихся в проектной документации, требованиям, установленным Техническим регламентом о безопасности зданий и сооружений?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "438035",
                "name": "проведение испытаний по сертифицированным методикам",
                "correct": false
            },
            {
                "id": "438036",
                "name": "разработка специальных технических условий",
                "correct": false
            },
            {
                "id": "438034",
                "name": "подготовка результатов исследований",
                "correct": true
            },
            {
                "id": "438038",
                "name": "моделирование сценариев возникновения опасных природных процессов и явлений",
                "correct": true
            },
            {
                "id": "438037",
                "name": "применение стандартов иностранных организаций",
                "correct": false
            },
            {
                "id": "438033",
                "name": "проведение расчетов",
                "correct": true
            }
        ]
    },
    {
        "id": "437576",
        "name": "Какие разделы включаются в обязательном порядке в состав проектной документации для строительства линейных объектов?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437580",
                "name": "раздел \"Пояснительная записка\"",
                "correct": true
            },
            {
                "id": "437578",
                "name": "раздел \"Мероприятия по обеспечению доступа инвалидов\"",
                "correct": false
            },
            {
                "id": "437581",
                "name": "раздел \"Технологические и конструктивные решения линейного объекта. Искусственные сооружения\"",
                "correct": true
            },
            {
                "id": "437582",
                "name": "раздел \"Система водоотведения\"",
                "correct": false
            },
            {
                "id": "437577",
                "name": "раздел \"Мероприятия по охране окружающей среды\"",
                "correct": true
            },
            {
                "id": "437579",
                "name": "раздел \"Мероприятия по обеспечению пожарной безопасности\"",
                "correct": true
            }
        ]
    },
    {
        "id": "437894",
        "name": "К какой проектной документации Правительство Российской Федерации не устанавливает требования по составу и содержанию?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437896",
                "name": "применительно к отдельным этапам реконструкции",
                "correct": false
            },
            {
                "id": "437897",
                "name": "применительно к проектной документации, представляемой на экспертизу проектной документации",
                "correct": false
            },
            {
                "id": "437895",
                "name": "применительно к объектам благоустройства",
                "correct": true
            },
            {
                "id": "437898",
                "name": "применительно к перепланировке и переустройству помещений многоквартирных жилых домов",
                "correct": true
            }
        ]
    },
    {
        "id": "437452",
        "name": "К каким сведениям единого государственного реестра заключений экспертизы проектной документации объектов капитального строительства обеспечивается доступ всем заинтересованным лицам на бесплатной основе в сети \"Интернет\" в форме открытых данных?",
        "link": null,
        "answerIds": [],
        "correctly": false,
        "parent_id": null,
        "responses": [
            {
                "id": "437455",
                "name": "сведения о признании проектного решения, содержащегося в типовой проектной документации, типовым проектным решением ",
                "correct": true
            },
            {
                "id": "437454",
                "name": "сведения об индивидуальных предпринимателях и (или) юридических лицах, подготовивших проектную документацию, по результатам рассмотрения которой подготовлено заключение экспертизы",
                "correct": true
            },
            {
                "id": "437456",
                "name": "дата и номер договора об оказании услуг по проведению государственной экспертизы",
                "correct": false
            },
            {
                "id": "437457",
                "name": "дата заключения экспертизы",
                "correct": true
            },
            {
                "id": "437453",
                "name": "сведения о сроках проведения государственной экспертизы",
                "correct": false
            },
            {
                "id": "437458",
                "name": "должность эксперта (экспертов), проводившего государственную экспертизу",
                "correct": false
            }
        ]
    }
]


</script>