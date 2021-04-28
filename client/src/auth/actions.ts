import React from 'react';
import { Cookies } from 'react-cookie';
import {
  createAccessTokenCookie,
  createRefreshTokenCookie,
} from '../utils/authCookies';
import axiosInstance from '../utils/axiosInstance';
import { AuthAction } from './reducer';

const cookies = new Cookies();

interface ILoginPayload {
  email: string;
  password: string;
}

export async function loginUser(
  dispatch: React.Dispatch<AuthAction>,
  loginPayload: ILoginPayload
) {
  try {
    dispatch({ type: 'REQUEST_LOGIN' });
    const data = {
      accessToken: '',
      refreshToken: '',
      user: {
        id: '',
        username: '',
        email: '',
        joinedAt: '',
      },
    };
    let error = '';

    await axiosInstance
      .post('/login.php', loginPayload)
      .then((res) => {
        data.accessToken = res.data.accessToken;
        data.refreshToken = res.data.refreshToken;
      })
      .catch((err) => {
        error = String(err.message);
      });

    const tokenData = JSON.parse(atob(data.accessToken.split('.')[1])).data;

    data.user = { ...tokenData };

    if (data.accessToken && data.refreshToken && !error) {
      dispatch({ type: 'LOGIN_SUCCESS', payload: data });

      createAccessTokenCookie(data.accessToken);
      createRefreshTokenCookie(data.refreshToken);
      return data;
    }

    dispatch({ type: 'LOGIN_ERROR', error: error });
    return;
  } catch (error) {
    dispatch({ type: 'LOGIN_ERROR', error: error });
  }
}

export async function logout(dispatch: React.Dispatch<AuthAction>) {
  dispatch({ type: 'LOGOUT' });
  cookies.remove('access_token', { path: '/' });
  cookies.remove('refresh_token', { path: '/' });
}
