import { createRouter, createWebHistory } from 'vue-router';
import LandingPage from '../views/LandingPage.vue';
import Login from '../views/Login.vue';
import Dashboard from '../views/Dashboard.vue';
import { useAuthStore } from '../stores/auth';
import Roles from '../views/Roles.vue';
import Users from '../views/Users.vue';

const routes = [
  {
    path: '/',
    name: 'LandingPage',
    component: LandingPage,
    meta: { guestOnly: true }, // <-- META UNTUK TAMU
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {guestOnly: true}, 
},
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }, 
  },
  {
    path: '/roles',
    name: 'RoleManagement',
    component: Roles,
    meta: { requiresAuth: true }
  },
    {
    path: '/users',
    name: 'UserManagement',
    component: Users,
    meta: { requiresAuth: true, requiresAdmin: true } // <-- META UNTUK ADMIN
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  const isAuthenticated = authStore.isAuthenticated;

  // 1. Jika rute yang dituju butuh autentikasi
  if (to.meta.requiresAuth) {
    // Jika pengguna TIDAK login, paksa ke halaman Login
    if (!isAuthenticated) {
      return next({ name: 'Login' });
    }

    // Jika pengguna SUDAH login, periksa apakah butuh peran admin
    if (to.meta.requiresAdmin) {
      const isAdmin = authStore.user?.roles?.some(role => role.name === 'Administrator');
      // Jika butuh admin tapi pengguna BUKAN admin, lempar ke Dashboard
      if (!isAdmin) {
        return next({ name: 'Dashboard' });
      }
    }
  }

  // 2. Jika rute yang dituju hanya untuk tamu (belum login)
  if (to.meta.guestOnly && isAuthenticated) {
    // Jika pengguna sudah login, lempar ke Dashboard
    return next({ name: 'Dashboard' });
  }

  // 3. Jika semua pemeriksaan di atas lolos, izinkan navigasi
  return next();
});

export default router;