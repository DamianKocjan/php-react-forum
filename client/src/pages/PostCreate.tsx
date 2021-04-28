import React, { useEffect, useState } from 'react';
import { useMutation, useQuery } from 'react-query';
import { useHistory } from 'react-router';
import { useAuthState } from '../auth';
import axiosInstance from '../utils/axiosInstance';

function useCategories(): any {
  return useQuery('categories', async () => {
    const { data } = await axiosInstance.get('category/read.php');
    return data;
  });
}

const PostCreate: React.FC = () => {
  const history = useHistory();
  const { status, data, error } = useCategories();
  const { isLogged, user } = useAuthState();

  useEffect(() => {
    if (!isLogged) {
      try {
        history.goBack();
      } catch {
        history.push('/');
      }
    }
  }, []);

  useEffect(() => {
    if (!isLogged) {
      try {
        history.goBack();
      } catch {
        history.push('/');
      }
    }
  }, [isLogged]);

  type postType = {
    title: string;
    body: string;
    category: string;
    author: string;
  };

  const [postData, setPostData] = useState<postType>({
    title: '',
    body: '',
    category: data ? data[0] && data[0].id : '',
    author: user.id,
  });

  const mutation = useMutation(
    async (newPost: postType) =>
      await axiosInstance.post('post/create.php', newPost)
  );

  const handleSubmit = async (e: React.FormEvent) => {
    if (!isLogged) return;
    e.preventDefault();

    if (!postData.title || !postData.body || !postData.category) return;

    await mutation.mutateAsync(postData);

    if (mutation.isSuccess && mutation.isError === false) history.push('/');
  };

  const handleChange = (
    e: React.ChangeEvent<
      HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement
    >
  ) => {
    setPostData({
      ...postData,
      [e.target.name]: e.target.value.trim(),
    });
  };

  return (
    <div className="container px-5 py-24 mx-auto flex flex-col">
      <div className="mt-10 sm:mt-0">
        <div className="md:grid md:grid-cols-3 md:gap-6">
          <div className="mt-5 md:mt-0 md:col-span-2">
            <form method="POST" onSubmit={handleSubmit}>
              <div className="shadow overflow-hidden sm:rounded-md">
                <div className="px-4 py-5 bg-white sm:p-6">
                  <div className="grid grid-cols-6 gap-6">
                    <div className="col-span-6 sm:col-span-3">
                      <label
                        htmlFor="title"
                        className="block text-sm font-medium text-gray-700"
                      >
                        Title
                      </label>
                      <input
                        value={postData.title}
                        onChange={handleChange}
                        type="text"
                        name="title"
                        id="title"
                        className="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                      />
                    </div>

                    <div className="col-span-6 sm:col-span-3">
                      <label
                        htmlFor="body"
                        className="block text-sm font-medium text-gray-700"
                      >
                        Body
                      </label>
                      <div className="mt-1">
                        <textarea
                          value={postData.body}
                          onChange={handleChange}
                          id="body"
                          name="body"
                          rows={3}
                          className="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                          placeholder="..."
                          defaultValue={''}
                        />
                      </div>
                      <p className="mt-2 text-sm text-gray-500">
                        Content of your post
                      </p>
                    </div>

                    <div className="col-span-6 sm:col-span-3">
                      <label
                        htmlFor="category"
                        className="block text-sm font-medium text-gray-700"
                      >
                        Category
                      </label>
                      <select
                        value={postData.category}
                        onChange={handleChange}
                        id="category"
                        name="category"
                        className="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                      >
                        {status === 'loading' ? (
                          <option disabled>loading...</option>
                        ) : status === 'error' ? (
                          <option disabled>{error.message}</option>
                        ) : (
                          data.records.map(
                            (category: {
                              id: string;
                              title: string;
                              description: string;
                            }) => (
                              <option key={category.id} value={category.id}>
                                {category.title}
                              </option>
                            )
                          )
                        )}
                      </select>
                    </div>
                  </div>
                </div>
                <div className="px-4 py-3 bg-gray-50 text-right sm:px-6">
                  <button
                    type="submit"
                    className="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    Create
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  );
};

export default PostCreate;
