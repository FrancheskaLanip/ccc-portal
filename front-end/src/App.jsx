import { BrowserRouter, Routes, Route, Navigate } from "react-router-dom";
import Layout from "./components/Layout";
import Home from "./pages/Home";
import Login from "./pages/Login";
import ForgotPassword from "./pages/ForgotPassword";
import ResetPassword from "./pages/ResetPassword";
import Dashboard from "./pages/Dashboard";
import Students from "./pages/Students";
import Programs from "./pages/Programs";
import Users from "./pages/Users";
import Subjects from "./pages/Subjects";
import ClassSections from "./pages/ClassSections";
import Enrollments from "./pages/Enrollments";
import Announcements from "./pages/Announcements";
import MyClasses from "./pages/MyClasses";
import ChangePassword from "./pages/ChangePassword";

export default function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<Home />} />
        <Route path="/login" element={<Login />} />
        <Route path="/forgot-password" element={<ForgotPassword />} />
        <Route path="/reset-password" element={<ResetPassword />} />

        <Route element={<Layout />}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/students" element={<Students />} />
          <Route path="/programs" element={<Programs />} />
          <Route path="/users" element={<Users />} />
          <Route path="/subjects" element={<Subjects />} />
          <Route path="/class-sections" element={<ClassSections />} />
          <Route path="/enrollments" element={<Enrollments />} />
          <Route path="/announcements" element={<Announcements />} />
          <Route path="/my-classes" element={<MyClasses />} />
          <Route path="/password/change" element={<ChangePassword />} />
        </Route>

        <Route path="*" element={<Navigate to="/" replace />} />
      </Routes>
    </BrowserRouter>
  );
}