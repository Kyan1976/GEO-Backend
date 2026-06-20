'use client';

import React from 'react';
import { useRouter } from 'next/navigation';
import Register from '@/components/Register';
import type { LoginResponse } from '@/types/api';

export default function RegisterPage() {
  const router = useRouter();

  const handleLogin = ({ token: tk, user }: LoginResponse) => {
    localStorage.setItem('agd_token', tk);
    localStorage.setItem('agd_user', JSON.stringify(user || null));
    if (user?.id) localStorage.setItem('agd_user_id', String(user.id));
    router.push('/geo');
  };

  return <Register onLogin={handleLogin} />;
}
