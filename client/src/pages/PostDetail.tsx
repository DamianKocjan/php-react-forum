import React from 'react';
import { useQuery } from 'react-query';
import { useHistory, useParams } from 'react-router-dom';
import { useAuthState } from '../auth';
import axiosInstance from '../utils/axiosInstance';

const getPostById = async (id: string) => {
  const { data } = await axiosInstance.get('/post/read_one.php', {
    params: { id: id },
  });
  return data;
};

function usePost(id: string): any {
  return useQuery(['post', id], async () => await getPostById(id), {
    enabled: !!id,
  });
}

const PostDetail: React.FC = () => {
  const { id } = useParams<{ id: string }>();
  const { status, data, error } = usePost(id);
  const { isLogged, user } = useAuthState();
  const history = useHistory();

  const handleDelete = async () => {
    if (!isLogged && user.id !== data.author) return;

    await axiosInstance
      .post('/post/delete.php', { id: id })
      .catch((err) => console.log(err));

    history.push('/');
  };

  return (
    <>
      {status === 'loading' ? (
        <h1>loading...</h1>
      ) : status === 'error' ? (
        <h1>{error.message}</h1>
      ) : data ? (
        <section className="text-gray-600 body-font">
          <div className="container px-5 py-24 mx-auto flex flex-col">
            <div className="lg:w-4/6 mx-auto">
              <div className="flex flex-col sm:flex-row mt-10">
                <div className="sm:w-1/3 text-center sm:pr-8 sm:py-8">
                  <div className="w-20 h-20 rounded-full inline-flex items-center justify-center bg-gray-200 text-gray-400">
                    <svg
                      fill="none"
                      stroke="currentColor"
                      strokeLinecap="round"
                      strokeLinejoin="round"
                      strokeWidth="2"
                      className="w-10 h-10"
                      viewBox="0 0 24 24"
                    >
                      <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                      <circle cx="12" cy="7" r="4" />
                    </svg>
                  </div>
                  <div className="flex flex-col items-center text-center justify-center">
                    <h2 className="font-medium title-font mt-4 text-gray-900 text-lg">
                      {data.author_username}
                    </h2>
                    <div className="w-12 h-1 bg-indigo-500 rounded mt-2 mb-4" />
                    <p className="text-base">
                      {new Date(data.author_joinedAt).toLocaleDateString()}
                    </p>
                    <p className="text-base">
                      Category {data.category_title.toUpperCase()}
                    </p>
                    {data.author === user.id && (
                      <button onClick={handleDelete}>Delete</button>
                    )}
                  </div>
                </div>
                <div className="sm:w-2/3 sm:pl-8 sm:py-8 sm:border-l border-gray-200 sm:border-t-0 border-t mt-4 pt-4 sm:mt-0 text-center sm:text-left">
                  <h1 className="mb-4">{data.title}</h1>
                  <p className="leading-relaxed text-lg mb-4">{data.body}</p>
                </div>
              </div>
            </div>
          </div>
        </section>
      ) : null}
    </>
  );
};

export default PostDetail;
