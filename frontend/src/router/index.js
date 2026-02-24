import { createRouter, createWebHistory } from 'vue-router';

const ADMIN_TOKEN_KEY = 'anmi_admin_token';

const routes = [
  { path: '/', name: 'Home', component: () => import('../views/Home.vue'), meta: { title: 'Anmi Beatz' } },
  // Your private upload page. Not linked on the site. Change path here if you want (e.g. '/studio' or '/x7k2m').
  { path: '/admin', name: 'Admin', component: () => import('../views/Admin.vue'), meta: { title: 'Anmi Beatz' } },
  {
    path: '/admin/tracks',
    name: 'AdminTracks',
    component: () => import('../views/admin/Tracks.vue'),
    meta: { title: 'Anmi Beatz', requiresAdmin: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  if (!to.meta?.requiresAdmin) return true;
  const token = sessionStorage.getItem(ADMIN_TOKEN_KEY);
  if (token) return true;
  return { path: '/admin', query: { redirect: to.fullPath } };
});

router.afterEach((to) => {
  document.title = to.meta?.title || 'Anmi Beatz';
});

export default router;
