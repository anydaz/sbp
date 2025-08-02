import React from 'react';
import { BrowserRouter as Router, Link, Switch, Route } from 'react-router-dom';
import Login from '../pages/Login.js';
import Main from '../Main.js';
import AuthenticatedRoute from './AuthenticatedRoute.js';
import UnAuthenticatedRoute from './UnAuthenticatedRoute.js';

const AppRoute = () => {
	return(
		<Router>
	        <Switch>
	            <UnAuthenticatedRoute path="/login" component={Login}/>
	            <AuthenticatedRoute path="/" component={Main}/>
	        </Switch>
	    </Router>
    )
}

export default AppRoute;