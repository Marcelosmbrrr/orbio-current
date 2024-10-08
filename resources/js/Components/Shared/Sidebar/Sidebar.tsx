import * as React from "react";
import { Link, usePage } from "@inertiajs/react";
import { SidebarItem } from "./SidebarItem";
import { BatteryIcon } from "./../Icons/BatteryIcon";
import { DroneIcon } from "./../Icons/DroneIcon";
import { EquipmentIcon } from "./../Icons/EquipmentsIcon";
import { LogoutIcon } from "./../Icons/LogoutIcon";
import { ServiceOrderIcon } from "./../Icons/ServiceOrderIcon";
import { UserIcon } from "./../Icons/UserIcon";
import { UsersIcon } from "./../Icons/UsersIcon";
import { MapIcon } from "./../Icons/MapIcon";

export const Sidebar = React.memo(() => {
    const { auth } = usePage().props as { auth: any };

    function closeSidebar() {
        document.getElementById("sidebar")?.classList.add("hidden");
    }

    return (
        <aside
            id="sidebar"
            className="fixed top-0 left-0 z-40 w-56 h-screen transition-transform bg-gray-900 dark:bg-gray-900 border-r border-r-gray-800 hidden lg:block"
        >
            <div className="h-full px-3 py-4 overflow-y-auto">
                <ul className="space-y-2 font-medium">
                    <li className="lg:hidden">
                        <button
                            onClick={closeSidebar}
                            type="button"
                            className=""
                        >
                            <span className="sr-only">Open sidebar</span>
                            <svg
                                className="w-6 h-6 text-white"
                                aria-hidden="true"
                                fill="currentColor"
                                viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg"
                            >
                                <path
                                    clipRule="evenodd"
                                    fillRule="evenodd"
                                    d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"
                                ></path>
                            </svg>
                        </button>
                    </li>

                    <li>
                        <div className="flex items-center p-2 rounded-lg text-white">
                            <span className="text-2xl font-bold tracking-tight">
                                Orbio (alfa)
                            </span>
                        </div>
                    </li>

                    <li>
                        <div className="flex items-center p-2 rounded-lg text-white">
                            <span className="flex-1 whitespace-nowrap">
                                Módulos
                            </span>
                        </div>
                    </li>

                    {!!auth.user.authorization.managers.read && (
                        <SidebarItem
                            href="/managers"
                            icon={UsersIcon}
                            label="Gerentes"
                        />
                    )}

                    {!!auth.user.authorization.users.read && (
                        <SidebarItem icon={UsersIcon} label="Usuários">
                            <li>
                                <Link
                                    href={route("pilots.index")}
                                    className="flex items-center p-3 rounded-lg text-white hover:bg-gray-700 cursor-pointer"
                                >
                                    <div className="p-1 rounded">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            className="flex-shrink-0 w-5 h-5 text-white transition duration-75"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <span className="flex-1 ms-3 whitespace-nowrap">
                                        Pilotos
                                    </span>
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("clients.index")}
                                    className="flex items-center p-3 rounded-lg text-white hover:bg-gray-700 cursor-pointer"
                                >
                                    <div className="p-1 rounded">
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 24 24"
                                            fill="currentColor"
                                            className="flex-shrink-0 w-5 h-5 text-white transition duration-75"
                                        >
                                            <path
                                                fillRule="evenodd"
                                                d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                                                clipRule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <span className="flex-1 ms-3 whitespace-nowrap">
                                        Clientes
                                    </span>
                                </Link>
                            </li>
                        </SidebarItem>
                    )}

                    {!!auth.user.authorization.serviceorders.read && (
                        <SidebarItem
                            href={route("service-orders.index")}
                            icon={ServiceOrderIcon}
                            label="Ordens de Serviço"
                        />
                    )}

                    {!!auth.user.authorization.flightplans.read && (
                        <SidebarItem
                            href={route("flight-plans.index")}
                            icon={MapIcon}
                            label="Planos de Voo"
                        />
                    )}

                    {!!auth.user.authorization.equipments.read && (
                        <SidebarItem icon={EquipmentIcon} label="Equipamentos">
                            <li>
                                <Link
                                    href={route("drones.index")}
                                    className="flex items-center p-3 rounded-lg text-white hover:bg-gray-700 cursor-pointer"
                                >
                                    <div className="p-1 rounded">
                                        <DroneIcon className="flex-shrink-0 w-5 h-5 text-white transition duration-75" />
                                    </div>
                                    <span className="flex-1 ms-3 whitespace-nowrap">
                                        Drones
                                    </span>
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("batteries.index")}
                                    className="flex items-center p-3 rounded-lg text-white hover:bg-gray-700 cursor-pointer"
                                >
                                    <div className="p-1 rounded">
                                        <BatteryIcon className="flex-shrink-0 w-5 h-5 text-white transition duration-75" />
                                    </div>
                                    <span className="flex-1 ms-3 whitespace-nowrap">
                                        Baterias
                                    </span>
                                </Link>
                            </li>
                            <li>
                                <Link
                                    href={route("equipments.index")}
                                    className="flex items-center p-3 rounded-lg text-white hover:bg-gray-700 cursor-pointer"
                                >
                                    <div className="p-1 rounded">
                                        <EquipmentIcon className="flex-shrink-0 w-5 h-5 text-white transition duration-75" />
                                    </div>
                                    <span className="flex-1 ms-3 whitespace-nowrap">
                                        Outros
                                    </span>
                                </Link>
                            </li>
                        </SidebarItem>
                    )}

                    <li>
                        <div className="flex items-center p-2 rounded-lg text-white">
                            <span className="flex-1 whitespace-nowrap">
                                Outros
                            </span>
                        </div>
                    </li>
                    <SidebarItem
                        href="/profile"
                        icon={UserIcon}
                        label="Minha Conta"
                    />
                    <SidebarItem
                        method="post"
                        href={route("logout")}
                        icon={LogoutIcon}
                        label="Sair"
                    />
                </ul>
            </div>
        </aside>
    );
});
