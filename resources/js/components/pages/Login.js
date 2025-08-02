import React, { useState } from 'react';
import Api from '../../api.js';
import { useHistory } from "react-router-dom";

const Home = () => {
	const [email, setEmail] = useState("");
	const [password, setPassword] = useState("");
    const [isError, setError] = useState({show: false, message: ""})
	const history = useHistory();

	const login = async () => {
		const body = {
            email: email,
            password: password
        }
        const { data } = await Api("api/login", body, "POST", {
			errorFn: (error) => { setError({show: true, message: "Email atau password salah!"}) }
		});
        const token = data.access_token;

        localStorage.setItem("token", token);
        history.push("/");
	}

	return (
		<div className="flex flex-col justify-center items-center h-full">
			<h1 className="font-bold text-center text-2xl mb-5">Login</h1>
			<div className="bg-white shadow w-1/4 rounded-lg divide-y divide-gray-200">
				<div className="px-5 py-7">
					{ isError.show &&
						<button type="button" class="w-full px-4 mb-5 bg-red-600 text-white p-2 rounded leading-none flex items-center justify-center text-sm">
							{isError.message}
						</button>
					}
        			<label className="font-semibold text-sm text-gray-600 pb-1 block">E-mail</label>
        			<input
        				type="text"
        				className="border rounded-lg px-3 py-2 mt-1 mb-5 text-sm w-full"
        				onChange={(e) => setEmail(e.target.value)}
        			/>
        			<label className="font-semibold text-sm text-gray-600 pb-1 block">Password</label>
        			<input
        				type="password"
        				className="border rounded-lg px-3 py-2 mt-1 mb-5 text-sm w-full"
        				onChange={(e) => setPassword(e.target.value)}
        			/>
        			<button
        				type="button"
        				className="transition duration-200 bg-blue-500 hover:bg-blue-600 text-white w-full py-2.5 rounded-lg text-sm font-semibold text-center inline-block"
        				onClick={login}
        			>
			            <span className="inline-block mr-2">Login</span>
			        </button>
        		</div>
			</div>
		</div>
	)
}

export default Home;