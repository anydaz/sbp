import React, {Fragment, useState, useEffect} from 'react';
import Header from '../shared/Header.js';
import TextField from '../shared/TextField.js';
import Api from '../../api.js';
import ModalInfo from 'components/shared/ModalInfo.js';
import { useHistory, useParams } from 'react-router-dom';

const CreateCustomer = () => {
	const [name, setName] = useState(null);
	const [email, setEmail] = useState(null);
	const [phone, setPhone] = useState(null);
    const [address, setAddress] = useState(null);

	const [showModal, setShowModal] = useState(false);

	const history = useHistory();
    const { id } = useParams();
	
	const handleSave = async() => {
		const body = {
			name: name,
			email: email,
            phone: phone,
			address: address,
		}

        const method = id ? "PUT" : "POST";
		const url = id ? `/api/customers/${id}` : "/api/customers"

		const response = await Api(url, body, method)
		if(response) {
			setShowModal(true);
			setTimeout(() => {
				history.push("/customer");
			}, 1000);
		}
	}

    const getCustomerData = async () => {
		const response = await Api(`/api/customers/${id}`);
		if(response){
			const customer = response.data;
			setName(customer.name);
			setAddress(customer.address);
			setPhone(customer.phone);
			setEmail(customer.email);
		}
	}

    useEffect(() => {
		// Check if current route is edit page or not
		// if edit page (has params id) then get product data
		if(id){
			getCustomerData();
		}
	}, [])

	return (
		<Fragment>
			<Header
				withBackButton={true}
				title='Buat Customer'
				action={{title: 'Save', onClick: handleSave }}
			/>
			<div className="p-4">
				<TextField
					value={name}
					label="Nama"
					onChange={(e) => {setName(e.target.value)}}
				/>
				<TextField
					value={email}
					label="Email"
					onChange={(e) => {setEmail(e.target.value)}}
				/>
                <TextField
					value={phone}
					label="No Telepon"
					onChange={(e) => {setPhone(e.target.value)}}
				/>
                <TextField
					value={address}
					label="Alamat"
					onChange={(e) => {setAddress(e.target.value)}}
				/>
			</div>
			{showModal && <ModalInfo/>}
		</Fragment>
	)
}

export default CreateCustomer;