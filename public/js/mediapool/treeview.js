document.addEventListener("DOMContentLoaded", function(event)
{
	let nodesModel = new NodesModel();

	const tree = new mar10.Wunderbaum({
		debugLevel: 3,
		element: document.getElementById("mediapool-tree"),
		source: { url: '/async/mediapool/node/0' },
		selectMode: "single",
		init: (e) => {
		},
		lazyLoad: function (e) {
			return { url: '/async/mediapool/node/'+ e.node.key, params: { parentKey: e.node.key } };
		},
		filter: {autoApply: true, mode: "hide"},
	});

	const tree_filter = document.getElementById("tree_filter");
	tree_filter.addEventListener("input", (event) => {
		tree.filterNodes(event.target.value, { mode: "hide" });
	})

	const close_dialog_button = document.getElementById("close_dialog_button");
	close_dialog_button.addEventListener("click", () => {
		const dialog = document.getElementById("editFolderDialog");
		dialog.close("cancel");
	});
	const editFolderDialog = document.getElementById('editFolderDialog');
	const addRootFolder = document.getElementById('addRootFolder');
	addRootFolder.addEventListener('click', () => {
		editFolderDialog.showModal();
		editFolderDialog.querySelectorAll('input, textarea').forEach(input => input.value = '');
		document.getElementById("parent_node_id").value = 0
	});

	editFolderDialog.addEventListener('close', () => {
		if (editFolderDialog.returnValue === "submit")
		{
			(async () => {

				const folder_name = document.getElementById("folder_name").value;

				const apiUrl = '/async/mediapool/node';
				const dataToSend = {"parent_id": 0, "name": folder_name};
				const options = {method: 'POST',headers: {'Content-Type': 'application/json'},	body: JSON.stringify(dataToSend)}

				const result = await nodesModel.fetchData(apiUrl, options).catch(error => {
					console.error('Fetch error:', error.message);
					return null;
				});

				if (!result || !result.success)
				{
					console.error('Error:', result?.error_message || 'Unknown error');
					return;
				}

				tree.addChildren({ key:  result.data.node_id, title: folder_name, isFolder: true });
			})();

		}
	});

	const template_context_menu_tree = document.getElementById('context_menu_tree');
	const tree_element = document.getElementById("mediapool-tree");
	tree_element.addEventListener("contextmenu", (event) => {
		event.preventDefault();

		const menu = document.querySelector('#context_menu_tree').content.cloneNode(true).firstElementChild;
		document.querySelectorAll('.context_menu').forEach(el => el.remove());  // remove all rpevious menu
		menu.style.left = `${event.pageX}px`;
		menu.style.top = `${event.pageY}px`;
		document.body.appendChild(menu);
		document.addEventListener('click', () => menu.remove(), { once: true });

		const node = mar10.Wunderbaum.getNode(event.target);
		tree.setActiveNode(node);
		console.log(node.title, node.key);

		const delete_node = document.getElementById("delete_node");
		delete_node.addEventListener("click", (event) => {
			(async () => {

				const apiUrl = "/async/mediapool/node";
				const dataToSend = {"node_id": node.key};
				const options = {method: 'DELETE',headers: {'Content-Type': 'application/json'},	body: JSON.stringify(dataToSend)}

				const result = await nodesModel.fetchData(apiUrl, options).catch(error => {
					console.error('Fetch error:', error.message);
					return null;
				});

				if (!result || !result.success)
				{
					console.error('Error:', result?.error_message || 'Unknown error');
					return;
				}

				node.remove();
			})();


		});

	});


});

class NodesModel
{
	async fetchData(url, options = {})
	{
		try
		{
			const response = await fetch(url, options);

			if (!response.ok)
			{
				throw new Error(`HTTP-Error: ${response.status}`);
			}

			return await response.json();
		}
		catch (error)
		{
			console.error('Fetch error:', error);
			throw error;
		}
	}
}

