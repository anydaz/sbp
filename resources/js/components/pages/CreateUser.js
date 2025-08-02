import React, {Fragment, useState} from 'react';
import Header from '../shared/Header.js';
import TextField from '../shared/TextField.js';
import Dropdown from 'components/shared/Dropdown.js';
import Api from '../../api.js';
import { useHistory } from 'react-router-dom';
import ModalInfo from 'components/shared/ModalInfo.js';

const DEFAULT_ROLE = ['sales', 'cashier', 'backoffice']

const CreateUser = () => {
	const [name, setName] = useState(null);
	const [email, setEmail] = useState(null);
	const [role, setRole] = useState(null);
	const [showModal, setShowModal] = useState(false);

	const history = useHistory();

	const handleSave = async() => {
		const body = {
			name: name,
			email: email,
			role: role,
		}

		const response = await Api("/api/users", body, "POST")
		if(response){
			setShowModal(true);
			setTimeout(() => {
				history.push("/user");
			}, 1000);
		}
	}

	return (
		<Fragment>
			<Header
				withBackButton={true}
				title='Buat User'
				action={{title: 'Save', onClick: handleSave }}
			/>
			<div className="p-4">
				<TextField
					value={name}
					label="Nama User"
					onChange={(e) => {setName(e.target.value)}}
				/>
				<TextField
					value={email}
					label="Email User"
					onChange={(e) => {setEmail(e.target.value)}}
				/>
                <label class={`font-semibold text-xs text-gray-600 pb-1 block`}>
                    Role
                </label>
				<Dropdown
					selected={role}
                    options={DEFAULT_ROLE}
					onChange={(role) => {setRole(role)}}
				/>
			</div>
			{showModal && <ModalInfo/>}
		</Fragment>
	)
}

export default CreateUser;