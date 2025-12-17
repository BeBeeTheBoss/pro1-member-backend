import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout";

export default function Dashboard({user}) {
    return <AuthenticatedLayout user={user}>

    </AuthenticatedLayout>
}
