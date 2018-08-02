import axios, {AxiosInstance} from "axios";
import Vue from "vue";
import VueRouter from "vue-router";
import routes from "./routes";

Vue.use(VueRouter);

const protectRequests: AxiosInstance = axios.create({
  baseURL: "/api"
});

const router: VueRouter = new VueRouter({
  // mode: "history",
  routes,
  scrollBehavior(to, from, savedPosition) {
    return { x: 0, y: 0 };
  }
});

router.beforeEach((to, from, next) => {
  if (to.matched.some((record) => record.meta.public)) {
    console.log("не пуплик");
  }

  next();
});

export default router;
