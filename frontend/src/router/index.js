import { createRouter, createWebHistory } from 'vue-router';

const routes = [
  { path: '/', name: 'Home', component: () => import('../views/Home.vue'), meta: { title: 'Anmi Beatz' } },
  // Your private upload page. Not linked on the site. Change path here if you want (e.g. '/studio' or '/x7k2m').
  { path: '/admin', name: 'Admin', component: () => import('../views/Admin.vue'), meta: { title: 'Anmi Beatz' } },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.afterEach((to) => {
  document.title = to.meta?.title || 'Anmi Beatz';
});

export default router;
