import Api from '../../api.js';

const isAuthenticated = () => {
	const token = localStorage.getItem("token");
	return !!token;
}

export { isAuthenticated }
