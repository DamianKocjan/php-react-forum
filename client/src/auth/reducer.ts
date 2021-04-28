import { Cookies } from 'react-cookie';
import { IUser } from '../types';

const cookies = new Cookies();

export interface IAuthState {
  user: IUser;
  accessToken: string;
  refreshToken: string;
  loading: boolean;
  errorMessage: null | string;
  isLogged: boolean;
}

let accessTokenCookie = cookies.get('access_token');
let refreshTokenCookie = cookies.get('refresh_token');

let userData = { id: '', username: '', email: '', joinedAt: '' };
if (accessTokenCookie) {
  try {
    userData = JSON.parse(atob(accessTokenCookie.split('.')[1])).data;
  } catch (e) {
    accessTokenCookie = '';
    refreshTokenCookie = '';
  }
}

const user: IUser = {
  id: userData.id || '',
  username: userData.username || '',
  email: userData.email || '',
  joinedAt: userData.joinedAt || '',
};

export const initialState: IAuthState = {
  user: user,
  accessToken: accessTokenCookie || '',
  refreshToken: refreshTokenCookie || '',
  loading: false,
  errorMessage: null,
  isLogged: !!accessTokenCookie === true && !!refreshTokenCookie === true,
};

export interface IAuthPayload {
  user: IUser;
  accessToken: string;
  refreshToken: string;
}

export type AuthAction =
  | { type: 'REQUEST_LOGIN' | 'LOGOUT' }
  | { type: 'LOGIN_SUCCESS'; payload: IAuthPayload }
  | { type: 'LOGIN_ERROR'; error: string };

export const AuthReducer = (
  initialState: IAuthState,
  action: AuthAction
): IAuthState => {
  switch (action.type) {
    case 'REQUEST_LOGIN':
      return {
        ...initialState,
        loading: true,
      };
    case 'LOGIN_SUCCESS':
      return {
        ...initialState,
        user: action.payload.user,
        accessToken: action.payload.accessToken,
        refreshToken: action.payload.refreshToken,
        loading: false,
        isLogged: true,
      };
    case 'LOGOUT':
      return {
        ...initialState,
        user: {
          id: '',
          username: '',
          email: '',
          joinedAt: '',
        },
        accessToken: '',
        refreshToken: '',
        isLogged: false,
      };

    case 'LOGIN_ERROR':
      return {
        ...initialState,
        loading: false,
        errorMessage: action.error,
      };

    default:
      return initialState;
  }
};
