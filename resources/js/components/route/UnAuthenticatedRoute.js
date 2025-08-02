import React from 'react';
import { Redirect, Route, RouteProps } from 'react-router-dom';
import { isAuthenticated } from './helper.js';

const UnAuthenticatedRoute = ({ component: Component, path }) => {
  if (isAuthenticated()) {
  	console.log("test UnAuthenticatedRoute");
    return <Redirect to="/" />;
  }

  return <Route component={Component} path={path} />;
};

export default UnAuthenticatedRoute;