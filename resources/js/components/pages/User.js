import React, { Fragment, useState, useEffect } from 'react';
import Api from '../../api.js';
import Table from '../shared/Table.js';
import TextField from '../shared/TextField.js';
import Button from '../shared/Button.js';
import Header from '../shared/Header.js';
import { useTable, usePagination } from 'react-table';
import { useHistory } from 'react-router-dom';
import useDebounce from 'hooks/useDebounce.js';

const User = () => {
	const [search, setSearch] = useState(null);
	const [users, setUsers] = useState([]);
	const [currentPage, setCurrentPage] = useState(1);
	const [lastPage, setLastPage] = useState(1);
	const history = useHistory();

	const getUsers = async () => {
		const response = await Api("/api/users", {search: search ? search : null})
		setCurrentPage(response.data.current_page);
		setLastPage(response.data.last_page);
		setUsers(response.data.data);
	}

	const goToPage = async(page) => {
		const response = await Api("api/users", {search: search ? search : null, page: page})
		setCurrentPage(response.data.current_page);
		setLastPage(response.data.last_page);
		setUsers(response.data.data);
	}

	useEffect(() => {
		getUsers();
	}, [search]);


	const columns = React.useMemo(() => [
		{Header: 'Name',accessor: 'name'},
		{Header: 'Role',accessor: 'role'},
	], []);

	const tableInstance = useTable({ columns, data: users }, usePagination);
	const debounceInput = useDebounce((text) => setSearch(text), 500);

	return (
		<Fragment>
			<Header
				title='User'
				action={{title: 'Buat User Baru', onClick: () => history.push('/user/create')}}
			/>
			<div className="p-4">
				<div className="w-2/5">
					<TextField
						label="Cari User"
						onChange={(e) => debounceInput(e.target.value)}
					/>
				</div>
				<Table
					currentPage={currentPage}
					lastPage={lastPage}
					goToPage={goToPage}
					tableInstance={tableInstance}
				/>
			</div>
		</Fragment>
	)
}

export default User;