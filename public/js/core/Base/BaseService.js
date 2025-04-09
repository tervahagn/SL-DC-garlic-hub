export class BaseService
{
	#fetchClient       = null;

	constructor(fetchClient)
	{
		this.#fetchClient      = fetchClient;
	}

	/**
	 * as JavaScrpt do not have protected methods, we use the old "private" workaround
	 */
	async _sendRequest(url, method, data)
	{
		let options = {};

		if (method === "GET")
			options = {method, headers: { 'Content-Type': 'application/json' }};
		else
			options = {method, headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data)};

		const result  = await this.#fetchClient.fetchData(url, options).catch(error => {
			throw new Error(error.message);
		});

		if (!result || !result.success)
			throw new Error(result.error_message);

		return result;
	}
}