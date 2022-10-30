import { lazy } from "react";
import { createBrowserRouter } from "react-router-dom";

const App = lazy(() => import("./App"))

const router = createBrowserRouter([
    {
      path: '/',
      element: <App />
    }
  ])
export default router;