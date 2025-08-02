import React from 'react';
import { Redirect, Route, RouteProps } from 'react-router-dom';
import { isAuthenticated } from './helper.js';

const AuthenticatedRoute = ({ component: Component, path }) => {
  if (!isAuthenticated()) {
    return <Redirect to="/login" />;
  }

  return <Route component={Component} path={path} />;
};

export default AuthenticatedRoute;