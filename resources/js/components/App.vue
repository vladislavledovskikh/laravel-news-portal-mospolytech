<template>
    <!--
        Всплывающее окно видно только тогда, когда в article есть данные,
        то есть когда по каналу вещания пришло событие.
    -->
    <transition name="notice">
        <div v-if="article" class="notice" role="alertdialog" aria-live="polite">
            <div class="notice__text">
                <p class="notice__label">Новая новость</p>
                <p class="notice__title">
                    <a :href="article.url" class="notice__link">{{ article.name }}</a>
                </p>
            </div>

            <button type="button" class="notice__close" aria-label="Закрыть" @click="close">
                &times;
            </button>
        </div>
    </transition>
</template>

<script>
export default {
    name: 'App',

    data() {
        return {
            /** Данные о новости из события; пока null — окно скрыто. */
            article: null,
            /** Таймер автозакрытия окна. */
            hideTimer: null,
        };
    },

    created() {
        /*
         | Подписка на широковещание.
         | channel('articles')      — имя канала из NewArticleEvent::broadcastOn()
         | listen('NewArticleEvent') — имя из NewArticleEvent::broadcastAs()
         | payload.article           — данные из NewArticleEvent::broadcastWith()
         */
        if (!window.Echo) {
            return;
        }

        window.Echo.channel('articles').listen('NewArticleEvent', (payload) => {
            this.article = payload.article;
            this.scheduleHide();
        });
    },

    beforeUnmount() {
        clearTimeout(this.hideTimer);
    },

    methods: {
        close() {
            this.article = null;
            clearTimeout(this.hideTimer);
        },

        /** Через 10 секунд окно закрывается само, но клик по ссылке работает. */
        scheduleHide() {
            clearTimeout(this.hideTimer);
            this.hideTimer = setTimeout(() => {
                this.article = null;
            }, 10000);
        },
    },
};
</script>
