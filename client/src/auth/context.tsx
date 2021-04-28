import React, { createContext, useContext, useReducer } from 'react';
import { Cookies } from 'react-cookie';

import { IUser } from '../types';
import { AuthAction, AuthReducer, IAuthState, initialState } from './reducer';

const cookies = new Cookies();

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

const AuthStateContext = createContext<IAuthState>({
  user: user,
  accessToken: accessTokenCookie || '',
  refreshToken: refreshTokenCookie || '',
  loading: false,
  errorMessage: null,
  isLogged: !!accessTokenCookie === true && !!refreshTokenCookie === true,
});
const AuthDispatchContext = createContext<React.Dispatch<AuthAction> | any>({});

export function useAuthState(): IAuthState {
  const context = useContext(AuthStateContext);
  if (context === undefined) {
    throw new Error('useAuthState must be used within a AuthProvider');
  }

  return context;
}

export function useAuthDispatch(): React.Dispatch<AuthAction> {
  const context = useContext(AuthDispatchContext);
  if (context === undefined) {
    throw new Error('useAuthDispatch must be used within a AuthProvider');
  }

  return context;
}

interface IAuthProviderProps {
  children: React.ReactNode;
}

export const AuthProvider: React.FC<IAuthProviderProps> = ({
  children,
}: IAuthProviderProps) => {
  const [user, dispatch] = useReducer(AuthReducer, initialState);

  return (
    <AuthStateContext.Provider value={user}>
      <AuthDispatchContext.Provider value={dispatch}>
        {children}
      </AuthDispatchContext.Provider>
    </AuthStateContext.Provider>
  );
};
