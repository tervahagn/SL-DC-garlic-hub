document.addEventListener("DOMContentLoaded", function()
{
	let tree = new mar10.Wunderbaum({
								  id: "my-tree",
								  element: document.getElementById("categories_list"),
								  source: [
									  { title: "Node 1", folder: true, children: [
											  { title: "Child node 1" },
											  { title: "Child node 2" }
										  ]},
									  { title: "Node 2", folder: true, children: [
											  { title: "Another child node" }
										  ]}
								  ]
							  });
});