import React, { useEffect } from 'react';
import { Route, Switch } from 'react-router';

import { useAuthState } from './auth';
import Header from './components/Header';
import Home from './pages/Home';
import Login from './pages/Login';
import NotFound from './pages/NotFound';
import PostCreate from './pages/PostCreate';
import PostDetail from './pages/PostDetail';
import Register from './pages/Register';
import axiosInstance from './utils/axiosInstance';

const App: React.FC = () => {
  const { isLogged, accessToken } = useAuthState();

  useEffect(() => {
    if (accessToken) axiosInstance.defaults.headers.Authorization = accessToken;
    else axiosInstance.defaults.headers.Authorization = '';
  }, [isLogged]);

  return (
    <>
      <Header />
      <Switch>
        <Route exact path="/" component={Home} />
        <Route exact path="/post/:id" component={PostDetail} />
        <Route exact path="/create" component={PostCreate} />
        <Route exact path="/login" component={Login} />
        <Route exact path="/register" component={Register} />
        <Route path="*" component={NotFound} />
      </Switch>
    </>
  );
};

export default App;
