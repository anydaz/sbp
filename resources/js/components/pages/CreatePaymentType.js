import React, {Fragment, useState, useEffect} from 'react';
import Header from '../shared/Header.js';
import TextField from '../shared/TextField.js';
import Api from '../../api.js';
import ModalInfo from 'components/shared/ModalInfo.js';
import { useHistory, useParams } from 'react-router-dom';

const CreatePaymentType = () => {
	const [name, setName] = useState(null);
	const [code, setCode] = useState(null);

	const [showModal, setShowModal] = useState(false);

	const history = useHistory();
    const { id } = useParams();
	
	const handleSave = async() => {
		const body = {
			name: name,
			code: code,
		}

        const method = id ? "PUT" : "POST";
		const url = id ? `/api/payment_types/${id}` : "/api/payment_types"

		const response = await Api(url, body, method)
		if(response) {
			setShowModal(true);
			setTimeout(() => {
				history.push("/payment_type");
			}, 1000);
		}
	}

    const getPaymentTypeData = async () => {
		const response = await Api(`/api/payment_types/${id}`);
		if(response){
			const paymentType = response.data;
			setName(paymentType.name);
			setCode(paymentType.code);
		}
	}

    useEffect(() => {
		// Check if current route is edit page or not
		// if edit page (has params id) then get product data
		if(id){
			getPaymentTypeData();
		}
	}, [])

	return (
		<Fragment>
			<Header
				withBackButton={true}
				title='Buat Tipe Pembayaran'
				action={{title: 'Save', onClick: handleSave }}
			/>
			<div className="p-4">
				<TextField
					value={name}
					label="Nama"
					onChange={(e) => {setName(e.target.value)}}
				/>
				<TextField
					value={code}
					label="Kode"
					onChange={(e) => {setCode(e.target.value)}}
				/>
			</div>
			{showModal && <ModalInfo/>}
		</Fragment>
	)
}

export default CreatePaymentType;